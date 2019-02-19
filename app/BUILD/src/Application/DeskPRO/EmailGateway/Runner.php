<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\EmailGateway;

use Application\DeskPRO\App;
use Application\DeskPRO\Email\EmailAccount\AccountConfigInterface;
use Application\DeskPRO\Email\EmailAccount\IncomingAccount\GmailConfig;
use Application\DeskPRO\EmailGateway\Exception\ProcessingException;
use Application\DeskPRO\EmailGateway\Fetcher\AbstractFetcher;
use Application\DeskPRO\EmailGateway\Fetcher\BatchFetcher;
use Application\DeskPRO\EmailGateway\Reader\AbstractReader;
use Application\DeskPRO\EmailGateway\Reader\EzcReader;
use Application\DeskPRO\Entity\EmailAccount;
use Application\DeskPRO\Entity\EmailSource;
use Application\DeskPRO\Log\DelegateLogger;
use Application\DeskPRO\WorkerProcess\Job\IncomingEmailSupervisor;
use Carbon\Carbon;
use DeskPRO\Bundle\SystemBundle\Entity\SystemAlerts\Event\Email\IncomingEmailFailureEvent;
use DeskPRO\Bundle\SystemBundle\Entity\SystemAlerts\Event\Email\IncomingEmailSuccessEvent;
use DeskPRO\Component\Lock\PdoStore;
use DeskPRO\Component\Util\MathUtils;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Type;
use DpSys\LowError\SystemErrorHandler;
use Orb\Log\Filter\CallbackFormatter;
use Orb\Log\LogItem;
use Orb\Log\Writer\EmailGatewayLogWriter;
use Orb\Util\Arrays;
use Orb\Util\Numbers;
use Orb\Util\OptionsArray;
use Orb\Util\Util;
use Symfony\Component\Lock\Factory as LockFactory;
use Symfony\Component\Lock\Store\RetryTillSaveStore;

/**
 * This runs collection and processsing in accounts.
 */
class Runner
{
    /**
     * @var \Application\DeskPRO\Log\Logger
     */
    private $logger;

    /**
     * @var \Application\DeskPRO\Email\EmailAccount\EmailAccountManager
     */
    private $accountManager;

    /**
     * @var EzcReader
     */
    private $reader;

    /**
     * @var \Application\DeskPRO\Entity\EmailAccount[]
     */
    private $accounts;

    /**
     * @var bool
     */
    private $enableRetryScheduling = true;

    /**
     * @var int
     */
    private $maxRetryAttempts = 3;

    /**
     * @var \Orb\Log\Writer\ArrayWriter
     */
    private $logMessages;

    /**
     * When non-0, sets the PHP time limit per iteration.
     *
     * @var int
     */
    private $setTimeLimit = 0;

    /**
     * When non-0, sets when the email loop will break early when
     * DP_START_TIME has gone over.
     *
     * @var int
     */
    private $softTimeLimit = 0;

    /**
     * When non-0, sets when the email loop will break early
     * when this many messages have been processed;.
     *
     * @var int
     */
    private $messageLimit = 0;

    /**
     * @var int
     */
    private $messageCount = 0;

    /**
     * @var array
     */
    protected $fromHeaders;

    /**
     * Runner constructor.
     *
     * @throws \Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException
     * @throws \Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException
     * @throws \ezcBasePropertyNotFoundException
     * @throws \ezcBaseValueException
     */
    public function __construct()
    {
        $this->logger         = new \Application\DeskPRO\Log\Logger();
        $this->accountManager = App::$container->getEmailAccountManager();
        $this->reader         = App::getContainer()->getEmailEzcReaderFactory()->create();
    }

    /**
     * Set the PHP time limit for a single message. This uses set_time_limit()
     * and resets it every iteration.
     *
     * This is used as an infinite-loop type preventative measure. PHP will halt
     * the script, and whatever message that was being processed will be stuck in the 'inserted'
     * state.
     *
     * @param int $time_limit
     */
    public function setPhpTimeLimit($time_limit)
    {
        $this->setTimeLimit = $time_limit;
    }

    /**
     * @param bool $enabled
     */
    public function setRetryScheduling($enabled = true)
    {
        $this->enableRetryScheduling = $enabled;
    }

    /**
     * @param int $time_limit
     */
    public function setSoftTimeLimit($time_limit)
    {
        $this->softTimeLimit = $time_limit;
    }

    /**
     * @param int $limit
     */
    public function setMessageLimit($limit)
    {
        $this->messageLimit = $limit;
    }

    /**
     * @param $logger \Application\DeskPRO\Log\Logger
     */
    public function setLogger(\Application\DeskPRO\Log\Logger $logger)
    {
        $this->logger = $logger;
    }

    /**
     * Set the accounts to process.
     *
     * @param EmailAccount[] $accounts
     */
    public function setAccounts(array $accounts)
    {
        $this->accounts = $accounts;
    }

    /**
     * Load accounts from the database.
     *
     * @param bool $include_disabled True to also include disabled account
     */
    public function loadAccountsFromDb($include_disabled = false)
    {
        if ($include_disabled) {
            $this->accounts = $this->accountManager->getAllAccounts('with_fetcher');
        } else {
            $this->accounts = $this->accountManager->getAllActiveAccounts('with_fetcher');
        }
    }

    /**
     * @param int $timeLimit The max time spent processing email before we break
     */
    public function execute($timeLimit = 0)
    {
        $execStart = time();

        if (!$timeLimit) {
            $timeLimit = 9999999999;
        }

        $this->logger->logDebug('Time limit: '.$timeLimit);

        if ($this->accounts) {
            $accounts = $this->accounts;
            if (!is_array($accounts) && $accounts instanceof \Traversable) {
                $accounts = iterator_to_array($accounts);
            }
            if (is_array($accounts)) {
                $accounts = array_values($accounts);

                // Sorting the accounts so that the oldest accounts are checked first
                // this round. This prevents some accounts from being 'skipped'
                // if checking is particularly slow (ie due to "Breaking, out of time" below)
                usort($accounts, function ($a, $b) {
                    $ad = Util::coalesce($a->date_last_incoming, $a->date_read_start, new \DateTime('-7 days'));
                    $bd = Util::coalesce($b->date_last_incoming, $b->date_read_start, new \DateTime('-7 days'));

                    if ($ad == $bd) {
                        return 0;
                    }

                    return $ad < $bd ? -1 : 1;
                });
            }

            foreach ($accounts as $account) {

                // only tickets supported at the moment
                if ($account->account_type != 'tickets') {
                    continue;
                }

                App::getDb()->avoidTimeout();
                $this->executeAccount($account, $timeLimit);

                $timeSoFar = time() - $execStart;
                $this->logger->logDebug('Time taken so far: '.$timeSoFar);

                if ($timeLimit && $timeSoFar >= $timeLimit) {
                    $this->logger->logDebug('Breaking, out of time');
                    break;
                }
            }
        }
    }

    /**
     * @param OptionsArray $result
     * @param bool         $is_retry
     *
     * @return bool
     */
    private function verifyCreatedObject(OptionsArray $result, $is_retry = false)
    {
        $this->logger->logDebug('Verifying created object...');

        if ($is_retry) {
            $this->logger->logDebug('-> Checking again');
        }

        if ($result->created_object_type == 'no_value') {
            $this->logger->logDebug('-> NoValue was returned (note: that is a valid return)');

            return true;
        }

        $id = $result->created_object_id;
        if (!$id) {
            $this->logger->logWarn('--> No object ID');

            return false;
        }

        $checkResult = false;

        switch ($result->created_object_type) {
            case 'ticket':
                $this->logger->logDebug("--> Verifying ticket {$id}");
                $t = App::$container->getDb()->fetchColumn('SELECT id FROM tickets WHERE id = ?', [$id]);
                if ($t) {
                    $this->logger->logDebug("--> Ticket {$id} OKAY");
                    $checkResult = true;
                } else {
                    $this->logger->logWarn("--> Ticket {$id} DOES NOT exist");
                    $checkResult = false;
                }
                break;

            case 'ticket_message':
                $this->logger->logDebug("--> Verifying ticket message {$id}");
                $t = App::$container->getDb()->fetchColumn('SELECT id FROM tickets_messages WHERE id = ?', [$id]);
                if ($t) {
                    $this->logger->logDebug("--> Ticket message {$id} OKAY");
                    $checkResult = true;
                } else {
                    $this->logger->logWarn("--> Ticket message {$id} DOES NOT exist");
                    $checkResult = false;
                }
                break;

            default:
                $this->logger->logWarn("--> Unknown object type: {$result->created_object_type}");

                return false;
        }

        if (!$checkResult && !$is_retry) {
            sleep(1);

            return $this->verifyCreatedObject($result, true);
        }

        return $checkResult;
    }

    /**
     * Called after all transactions are closed. This is a double-check
     * to make sure a source has the proper status applied to it, even in cases
     * where the doctrine entity manager is closed due to some critical error.
     *
     * @param EmailSource $source
     * @param array       $manual_set
     */
    private function ensureSourceStatus(EmailSource $source, array $manual_set = null)
    {
        global $DP_SET_SOURCE_STATUS;
        if (!$DP_SET_SOURCE_STATUS) {
            $DP_SET_SOURCE_STATUS = [];
        }

        $db = App::$container->getDb();
        $id = $source->id;

        if (!isset($DP_SET_SOURCE_STATUS[$id])) {
            $DP_SET_SOURCE_STATUS[$id] = [];
        }

        $DP_SET_SOURCE_STATUS[$id] = array_merge($DP_SET_SOURCE_STATUS[$id], [
            'status'      => $source->status,
            'error_code'  => $source->error_code,
            'source_info' => serialize($source->source_info ?: []),
        ]);

        if ($manual_set) {
            $DP_SET_SOURCE_STATUS[$id] = array_merge($DP_SET_SOURCE_STATUS[$id], $manual_set);
        }

        \DpShutdown::add(function () use ($db, $id) {
            global $DP_SET_SOURCE_STATUS;
            if (empty($DP_SET_SOURCE_STATUS[$id])) {
                return;
            }

            $set = $DP_SET_SOURCE_STATUS[$id];

            // These must not be in an active trans
            try {
                while ($db->isTransactionActive()) {
                    $db->commit();
                }
            } catch (\Exception $e) {
            }

            try {
                $db->update('email_sources', $set, ['id' => $id]);
            } catch (\Exception $e) {
                SystemErrorHandler::logException($e);
            }
        });
    }

    /**
     * Executes a single source. Good for re-processing.
     *
     * @param \Application\DeskPRO\Entity\EmailSource $source
     * @param AbstractReader                          $reader
     *
     * @throws \Exception
     *
     * @return bool
     */
    public function executeSource(EmailSource $source, AbstractReader $reader = null)
    {
        if (defined('DPC_SITE_FLAG_DISABLE_INMAIL')) {
            $source->status     = 'error';
            $source->error_code = 'rate_limit';
            App::getOrm()->persist($source);
            App::getOrm()->flush();

            return false;
        }

        if (!$this->logMessages) {
            $this->logMessages = new \Orb\Log\Writer\ArrayWriter();
            $this->logMessages->addFilter(new \Orb\Log\Filter\SimpleLineFormatter());
            $this->logger->addWriter($this->logMessages);
        }

        $sourceLogger = new DelegateLogger($this->logger);
        $sourceLogger->enable();
        $sourceLogger->addFilter(new CallbackFormatter(function (LogItem $item) {
            $item['is_email_info'] = true;

            return $item;
        }));

        $isInTrans = App::getDb()->isTransactionActive();
        if (!$isInTrans) {
            $sourceLogger->logDebug('Note: Not called within a transaction');
        }

        $this->logMessages->clear();

        $previousLogText = null;
        if ($source->log_blob) {
            try {
                $previousLogText = App::$container->getBlobStorage()->copyBlobRecordToString($source->log_blob);

                if ($source->log_blob->content_type === 'application/gzip') {
                    $previousLogText = @gzdecode($previousLogText) ?: '';
                }
            } catch (\Exception $e) {
            }
        }

        ++$source->exec_count;

        $sourceLogger->logDebug('Executing Source '.$source->getId());
        if ($source->uid) {
            $sourceLogger->logDebug('Email UID '.$source->uid);
        }
        $sourceLogger->logDebug('Attempt: '.$source->exec_count);

        // Attempt to detect if we should break due to memory
        $memUsage = memory_get_usage();
        $avail    = MathUtils::parseByteSize(@ini_get('memory_limit'));
        if ($memUsage && $memUsage > 0 && $avail && $avail > 0) {
            $remain = $avail - $memUsage;
            $min    = max(10485760, $source->blob->filesize * 4);
            $room   = $remain - $min;

            $sourceLogger->logDebug(sprintf('Memory Used: %d    Memory Max: %d    Est Memory Required: %d    Est Memory After: %d', $memUsage, $avail, $min, $room));

            if ($remain < $min) {
                $sourceLogger->log(sprintf('Detected that we are at the memory limit, quitting run'), 'debug');
                throw new ProcessingException('Detected that we are at the memory limit', ProcessingException::MEMORY_LIMIT);
            }
        }

        // Mark as processing now
        $sourceLogger->logDebug('Marking source as processing');
        $source->status = 'processing';
        App::getOrm()->persist($source);
        App::getOrm()->flush();

        $allowRetry = $this->enableRetryScheduling;
        $sourceLogger->logInfo('Retrying is '.($allowRetry ? 'on' : 'off'));
        if ($allowRetry && $source->exec_count >= $this->maxRetryAttempts) {
            $allowRetry = false;
            $sourceLogger->logInfo("--> Retrying turned off, max count reached: {$source->exec_count} >= {$this->maxRetryAttempts}");
        }

        $sourceLogger->logDebug('Running processors');
        $runnerExec = new RunnerExecSource(
            $source,
            $reader,
            $this->accountManager,
            $sourceLogger
        );
        $runnerExec->setFromHeaders($this->getFromHeaders());

        $didRollback = false;
        $doRetry     = false;
        try {
            $result = $runnerExec->run();
            App::$container->getEm()->flush();
            $sourceLogger->logDebug('--> Processors complete');

            if (!$isInTrans && App::getDb()->isTransactionActive()) {
                $sourceLogger->log('WARNING: Unclosed transaction!', 'info');
                $e = new \RuntimeException('WARNING: Unclosed transaction');
                SystemErrorHandler::logException($e, false, 'unclosed_trans_gateway');
                while (App::getDb()->isTransactionActive()) {
                    App::getDb()->commit();
                }
            }
        } catch (\Exception $e) {
            $message = substr($e->getMessage(), 0, 500);
            $sourceLogger->logDebug("--> Processor exception: {$e->getCode()} ".$message);
            $result = [
                'status'      => 'error',
                'error_code'  => 'server_error',
                'source_info' => [
                    'exception' => get_class($e),
                    'message'   => $message,
                    'code'      => $e->getCode(),
                    'trace'     => SystemErrorHandler::formatBacktrace($e->getTrace()),
                ],
            ];

            if ($allowRetry) {
                $doRetry = true;
                if (strpos(strtolower($e->getMessage()), 'deadlock') !== false) {
                    SystemErrorHandler::logException($e, true);
                }
            } else {
                $sourceLogger->logWarn('Not trying again (allow_retry is false)');
                SystemErrorHandler::logException($e, true);
            }

            if (App::getDb()->isTransactionActive()) {
                App::getDb()->rollback();
                $didRollback = true;
            }
        }

        $result = new OptionsArray($result);

        // Verify object
        if ($result->status == 'okay') {
            if (!$this->verifyCreatedObject($result)) {
                $newResult = new OptionsArray([
                    'status'      => 'error',
                    'error_code'  => 'server_error',
                    'source_info' => [
                        'Failed to verify created object',
                        'Expected: '.$result->created_object_type.' '.$result->created_object_id,
                    ],
                ]);

                $result = $newResult;

                if ($allowRetry) {
                    $doRetry = true;
                } else {
                    $sourceLogger->logWarn('Not trying again (allow_retry is false)');
                }
            }
        }

        if ($reader && $subj = $reader->getSubject()->getSubjectUtf8()) {
            $source->header_subject = $subj;
        }

        switch ($result->status) {
            case 'okay':
                $returnResult        = true;
                $source->status      = 'complete';
                $source->error_code  = null;
                $source->source_info = $result->source_info ?: [];
                $source->object_type = $result->created_object_type;
                $source->object_id   = $result->created_object_id;
                $source->object_info = $result->created_object_info;
                $sourceLogger->logInfo("Status: COMPLETE {$source->error_code}");
                break;

            case 'rejected':
                $returnResult        = true;
                $source->status      = 'rejected';
                $source->error_code  = $result->error_code ?: 'server_error';
                $source->source_info = $result->source_info ?: [];
                $sourceLogger->logError("Status: REJECTED {$source->error_code}");
                break;

            case 'rejected_soft':
                $returnResult        = true;
                $source->status      = 'rejected_soft';
                $source->error_code  = $result->error_code ?: 'server_error';
                $source->source_info = $result->source_info ?: [];
                $sourceLogger->logError("Status: REJECTED SOFT {$source->error_code}");
                break;

            case 'error':
                $returnResult        = false;
                $source->status      = 'error';
                $source->error_code  = $result->error_code ?: 'server_error';
                $source->source_info = $result->source_info ?: [];
                $sourceLogger->logError("Status: ERROR {$source->error_code}");
                break;

            default:
                $returnResult        = true;
                $source->status      = 'error';
                $source->error_code  = $result->error_code ?: 'server_error';
                $source->source_info = $result->source_info ?: [];
                $sourceLogger->logWarn("Unknown status type: {$result->status}");
                break;
        }

        if ($doRetry) {
            $sourceLogger->logInfo('Scheduling a retry -- status set to inserted');
            $source->status = 'retry';
        }

        if ($source->status === 'rejected' && App::getSetting('core.email_source_alert_rejection')) {
            $mailer  = App::getMailer();
            $message = $mailer->createMessage();
            $message->setTo(App::getSetting('core.email_source_alert_rejection'));
            $message->setSubject('Rejected: '.$reader->getSubject()->getSubjectUtf8());
            $message->getHeaders()->addTextHeader('Auto-Submitted', 'auto-generated');
            $message->getHeaders()->addTextHeader('X-Auto-Response-Suppress', 'All');
            $message->getHeaders()->addTextHeader('X-DeskPRO-Build', DP_BUILD_TIME); // used if this were to come back to us, prevents loops
            $hdUrl       = App::getContainer()->getBrandSetting('core.deskpro_url');
            $downloadUrl = $source->blob->getDownloadUrl(true);
            $toList      = implode(', ', array_map(function ($t) {
                return trim($t->getNameUtf8().' <'.$t->getRealEmail().'>');
            }, $reader->getToAddresses()));
            $body = <<<BODY
Subject:  {$reader->getSubject()->getSubjectUtf8()}
From:     {$reader->getFromAddress()->getNameUtf8()} <{$reader->getFromAddress()->getEmail()}>
To:       {$toList}
Rejected: {$source->error_code}

Download the raw email here:
$downloadUrl

View more information about this email online:
{$hdUrl}admin/#/emails/ticket_accounts/incoming-email/{$source->id}
BODY;
            $message->setBody($body);
            $mailer->send($message);
        }

        $this->ensureSourceStatus($source);

        $logMessages = $this->logMessages->getMessagesAsString();

        if ($previousLogText) {
            $logMessages = $previousLogText."\n\n\n".str_repeat('-', 80)."\n\n\n".$logMessages;
        }

        try {
            $sourceLogger->logDebug('Saving log blob...');
            $logBlobRow = App::$container->getBlobStorage()->createBlobRowFromString(
                $logMessages,
                'email-process.log',
                'plain/text',
                ['tag' => 'logs.email_source_log', 'prefer_gzipped' => true]
            );
            $sourceLogger->logInfo("Log blob {$logBlobRow['id']}");

            $this->ensureSourceStatus($source, ['log_blob_id' => $logBlobRow['id']]);

            if (!$didRollback) {
                $blob               = App::$container->getEm()->find('DeskPRO:Blob', $logBlobRow['id']);
                $source['log_blob'] = $blob;
                try {
                    App::$container->getEm()->persist($blob);
                    App::$container->getEm()->flush();
                } catch (\Exception $e) {
                }
            }

            $savedLog = true;
        } catch (\Exception $e) {
            $savedLog = false;
        }

        if (!$savedLog) {
            $sourceLogger->logDebug('Couldnt save log blob, saving to source info instead');
            $source->source_info = array_merge($source->source_info, ['log' => $logMessages]);
            $this->ensureSourceStatus($source);
        }

        $source->clearRawSource();

        App::getOrm()->detach($source);
        $source = null;

        if ($reader) {
            $reader->_kill();
            $reader = null;
        }

        $sourceLogger->logDebug('ALL DONE');

        $this->logMessages->clear();

        gc_collect_cycles();

        return $returnResult;
    }

    /**
     * Execute an account.
     *
     * $time_limit is the max time before the while loop breaks. The method will usually continue to process mail
     * until there is no email left. If you specify a time limit then the process will break after $time_limit seconds.
     * Note this check is done after processing of a message, it does not abort. This means that it's possible the time
     * limit will be exceeded (e.g., time limit of 10, message starts processing at 9 seconds so it continues).
     *
     * @param \Application\DeskPRO\Entity\EmailAccount $account
     * @param int                                      $timeLimit   The max time spent processing email before we break
     * @param bool                                     $onlyCollect Only collect and save the emails, don't process them now
     *
     * @throws \Exception
     */
    public function executeAccount(EmailAccount $account, $timeLimit = 0, $onlyCollect = false)
    {
        $lockStore   = new RetryTillSaveStore(new PdoStore(App::$container->get('doctrine.dbal.default_connection')));
        $lockFactory = new LockFactory($lockStore);

        // there could be only one instance of email gateway runner
        // make sure there is no race conditions
        $lock = $lockFactory->createLock(
            'email-gateway-runner.'.$account->getId(),
            IncomingEmailSupervisor::TIMEOUT_INTERVAL
        );

        $lock->acquire(true);

        try {
            $this->doExecuteAccount($account, $timeLimit, $onlyCollect);
        } finally {
            $lock->release();
        }
    }

    /**
     * @param \Application\DeskPRO\Entity\EmailAccount $account
     * @param int                                      $timeLimit   The max time spent processing email before we break
     * @param bool                                     $onlyCollect Only collect and save the emails, don't process them now
     *
     * @throws \Exception
     */
    private function doExecuteAccount(EmailAccount $account, $timeLimit = 0, $onlyCollect = false)
    {
        /* @var \DpRun\DpEnv $DP_ENV */
        global $DP_ENV;

        // Settings
        $settings = App::$container->get('settings_resolver')->getGlobalSettings();
        // get entity manager and connection
        $entityManager = App::$container->get('doctrine.orm.default_entity_manager');
        $dbConnection  = App::$container->get('doctrine.dbal.default_connection');
        // get event logger
        $dpEventLogger = App::$container->get('dp_sys.alerts.event_logger');

        // get incoming account config
        $incomingAccount = $account->getIncomingAccount();

        // check if email account has incoming account config
        if (!$incomingAccount instanceof AccountConfigInterface) {
            throw new \InvalidArgumentException(
                'Email account must have and incoming email account config'
            );
        }

        // Enable GC to log memory usage in benchmarks
        gc_enable();

        // start bechmark timer
        $startTime = microtime(true);

        // Email gateway log writer
        $emailGatewayLogWriter = new EmailGatewayLogWriter(
            $account,
            $dbConnection,
            App::$container->get('deskpro.blob_storage')
        );
        $this->logger->addWriter($emailGatewayLogWriter);

        // start logging
        $this->logger->log(
            "Start processing {$account->getAddress()} {$account->getIncomingAccountType()}",
            'info'
        );

        // stub
        $fetcher = null;

        try {
            // Mark email account as processing
            $this->markAccountAsProcessing($account);
            // attempt to create fetcher
            $fetcher = $this->createFetcher($account);
        } catch (\InvalidArgumentException $exception) {
            // mark account as no longer processing
            $this->markAccountAsNoLongerProcessing($account, false);
            // log failure
            $this->logger->log(
                "Failed to initialize fetcher for {$account->getAddress()} {$account->getIncomingAccountType()}: ".
                "{$exception->getMessage()}",
                'error'
            );
        }

        // ensure fetcher is created correctly even we didn't catch any exception
        if (!$fetcher instanceof AbstractFetcher) {
            // mark account as no longer processing
            $this->markAccountAsNoLongerProcessing($account, false);
            // log failure
            $this->logger->log(
                "Failed to initialize fetcher for {$account->getAddress()} {$account->getIncomingAccountType()}",
                'error'
            );
        }

        // Log fetcher initialization
        $this->logger->log(
            'Initialized fetcher of type: '.Util::getBaseClassname($fetcher),
            'info'
        );

        // set loggers
        $fetcher->setLogger($this->logger);

        if ($emailGatewayLogWriter instanceof EmailGatewayLogWriter) {
            // set gateway logger entity
            $fetcher->setEmailGatewayLogWriter($emailGatewayLogWriter);
        }

        // Define max size
        $maxSize = $settings->has('core.gateway_max_email')
            ? $settings->get('core.gateway_max_email')
            : 20971520; // @fixme: why not 41943040 as in config?

        // Configure fetcher
        $fetcher->setMaxSize($maxSize);

        $execStart  = time();
        $source     = null;
        $createdObj = null;
        $reader     = null;

        $insertedSourceIds = [];

        // Check whether we need to only collect emails or process them as well
        $onlyCollect = $onlyCollect || $DP_ENV->getConfig('async_email_processing.process');
        if (false === $onlyCollect) {
            /**
             * if we need to process already inserted emails, get them from database.
             * Those are marked as 'inserted' or 'retry'.
             */
            $query = 'SELECT `id` FROM `email_sources`
                WHERE `status` IN (:states) AND `email_account_id` = :id
                ORDER BY id ASC';

            $params = ['states' => ['inserted', 'retry'], 'id' => $account->getId()];
            $types  = ['states' => Connection::PARAM_STR_ARRAY, 'id' => \PDO::PARAM_INT];

            $insertedSourceIds = $dbConnection->fetchAllCol($query, $params, $types);

            $this->logger->log(
                sprintf('%d inserted messages being processed first', count($insertedSourceIds)),
                'debug'
            );
        }

        $processedSourceIds = [];

        // array of messages up next (used with fetchers that return a batch)
        $nextUp           = [];
        $doCheckNextBatch = true;

        while (true) {
            // All records should be flushed
            // Make sure there are no orphaned records
            $entityManager->flush();

            // Protection against nested transactions.
            // This should not be needed, but its a safety against unclosed transactions.
            // Without it, a mistake somewhere down the line can result in an entire
            // process of emails being rolledback.
            if ($dbConnection->isTransactionActive()) {
                $this->logger->log('WARNING: Unclosed transaction!', 'info');
                $dpEventLogger->logAloud(
                    new \RuntimeException(
                        'WARNING: Unclosed transaction. Sources processed: '.implode(', ', $processedSourceIds)
                    )
                );
                while ($dbConnection->isTransactionActive()) {
                    $dbConnection->commit();
                }
            }

            // Check if we need to stop after specific messages limit
            if ($this->messageLimit > 0) {
                if ($this->messageCount >= $this->messageLimit) {
                    $this->logger->logWarn(
                        sprintf('Hit message limit, breaking :: Processed %d messages', $this->messageCount)
                    );
                    break;
                }
            }

            $m = memory_get_usage();

            if (null !== ($nextInsertedId = array_shift($insertedSourceIds))) {
                $this->logger->log(
                    sprintf('Processing next inserted message: %d', $nextInsertedId),
                    'debug'
                );
                // get next inserted email from database
                $source = $entityManager->find('DeskPRO:EmailSource', $nextInsertedId);
            } elseif (count($nextUp) > 0 && (null !== ($nextReady = array_shift($nextUp)))) {
                $this->logger->log(
                    sprintf('Processing next inserted message'),
                    'debug'
                );
                // set current source to next message
                $source = $nextReady;
            } else {
                try {
                    // add session writer if not added
                    if ($emailGatewayLogWriter instanceof EmailGatewayLogWriter &&
                        !$this->logger->getWriterChain()->hasWriter($emailGatewayLogWriter)
                    ) {
                        $this->logger->addWriter($emailGatewayLogWriter);
                    }

                    $ts = microtime(true);
                    if ($fetcher instanceof BatchFetcher) {
                        if ($doCheckNextBatch) {
                            $batchLimit = 10;

                            $this->logger->log(
                                "BatchFetcher: fetching batch of {$batchLimit}",
                                'debug'
                            );

                            $nextUp     = $fetcher->readBatch('ticket', $batchLimit);
                            $batchCount = count($nextUp);

                            $this->logger->log(
                                "BatchFetcher: fetched batch of {$batchCount}",
                                'debug'
                            );

                            $source = array_shift($nextUp);

                            // only try another batch if we got a full batch last time
                            $doCheckNextBatch = ($batchCount >= $batchLimit);
                        } else {
                            $nextUp = [];
                            $source = null;
                        }
                    } else {
                        $source = $fetcher->readNext();
                    }

                    $this->logger->log(
                        sprintf('Read took %.3fs', microtime(true) - $ts),
                        'debug'
                    );

                    if (is_null($source)) {
                        $this->logger->log(
                            'No more messages in inbox',
                            'debug'
                        );

                        // If this is the first time we've reached the end
                        // save a start date to the account
                        if (is_null($account->date_read_start)) {
                            $account->date_read_start = new \DateTime('-10 days');
                            $entityManager->persist($account);
                            $entityManager->flush();
                        }

                        break;
                    }

                    // Log success event
                    $dpEventLogger->log(new IncomingEmailSuccessEvent($account));
                } catch (\Exception $e) {
                    // Log exception
                    $this->logger->log(
                        sprintf('readNext exception: %s', $e->getMessage()),
                        'info'
                    );

                    // Log failure event
                    $dpEventLogger->logAloud(new IncomingEmailFailureEvent($account, $e));

                    break;
                }
            }

            $processedSourceIds[] = $source->getId();

            if (!$this->logMessages) {
                $this->logMessages = new \Orb\Log\Writer\ArrayWriter();
                $this->logger->addWriter($this->logMessages);
            }

            $this->logMessages->clear();

            // remove session writer if added
            if ($emailGatewayLogWriter instanceof EmailGatewayLogWriter &&
                $this->logger->getWriterChain()->hasWriter($emailGatewayLogWriter)
            ) {
                $this->logger->removeWriter($emailGatewayLogWriter);
            }

            if ($this->setTimeLimit) {
                @set_time_limit($this->setTimeLimit);
            }

            $this->logger->log(
                "[Account {$account['id']}] Read source ID {$source['id']}",
                'debug'
            );

            // Already marked as an error (e.g., message too big) so we dont
            // process it through the account handlers
            if ($source->getStatus() === 'error') {
                $this->logger->log(
                    sprintf('Source marked as error :: %s', $source->getErrorCode()),
                    'debug'
                );

                // Send alert to user
                if ($source->getErrorCode() === EmailSource::ERR_MESSAGE_TOO_BIG) {
                    $reader = $this->reader;
                    $reader->setRawSource($source->headers."\n\nBogus Body\n");
                    $fromEmail = $reader->getFromAddress()->getEmail();
                    $subject   = $reader->getSubject()->getSubjectUtf8();

                    if ($fromEmail and $subject) {
                        $this->logger->log('Sending too-big email response', 'debug');

                        if (App::getContainer()->get('deskpro.feature_flags')->hasBeta('email_templates')) {
                            $viewModel = App::getContainer()->get('email.user_viewmodel_factory')
                                ->createEmailTooBigModel(
                                    $subject,
                                    Numbers::filesizeDisplay($maxSize)
                                );
                            App::getContainer()->get('email.email_sender')
                                ->send($viewModel, ['to' => $fromEmail]);
                        } else {
                            $message = App::getMailer()->createMessage();
                            $message->setTemplate('DeskPRO:emails_user:email-too-big.html.twig', [
                                'subject'  => $subject,
                                'max_size' => Numbers::filesizeDisplay($maxSize),
                            ]);
                            $message->setTo($fromEmail);
                            App::getMailer()->send($message);
                        }
                    }
                }

                continue;
            }

            if ($onlyCollect && $source->getStatus() !== 'error' && $DP_ENV->getConfig('async_email_processing.process')) {
                /** @var \Application\EmailBundle\Incoming\ProcQueue\ProcQueueInterface $proc */
                $proc = App::getContainer()->get('in_email.proc_queue');
                try {
                    $this->logger->logDebug('Queueing message for processing');
                    $proc->enqueueNewEmail($source);
                } catch (\Exception $e) {
                    $this->logger->logError('Exception: '.$e->getMessage());
                    $source->status = 'retry';
                    App::$container->getDb()->update(
                        'email_sources',
                        ['status' => $source->status, 'date_status' => $source->date_status->format('Y-m-d H:i:s')],
                        ['id'     => $source->id]
                    );
                }
            }

            $isMemLimit = false;

            if (!$onlyCollect) {
                $this->logger->logDebug('START: executeSource('.$source->getId().')');
                $t = microtime(true);
                try {
                    $this->executeSource($source);
                } catch (ProcessingException $e) {
                    if ($e->getCode() == ProcessingException::MEMORY_LIMIT) {
                        $isMemLimit = true;
                    } else {
                        $this->logger->logError('Exception: '.$e->getMessage());
                    }
                }

                $this->logger->logDebug(sprintf('FINISH: executeSource('.$source->getId().') - %.4fs', microtime(true) - $t));
            }

            $mEnd  = memory_get_usage();
            $mDiff = $mEnd - $m;

            $this->logger->log(sprintf('Memory usage: %.2f MB (total: %.2f MB)', $mDiff / 1024 / 1024, $mEnd / 1024 / 1024), 'debug');

            $timeSoFar = time() - $execStart;
            if ($timeLimit && $timeSoFar >= $timeLimit) {
                $this->logger->logInfo('Hit time limit, breaking');
                break;
            }

            if ($isMemLimit) {
                $this->logger->logInfo('Hit memory limit, breaking');
                break;
            }

            ++$this->messageCount;

            if ($this->softTimeLimit) {
                $t = microtime(true) - DP_START_TIME;
                if ($t > $this->softTimeLimit) {
                    $this->logger->logWarn(sprintf('Hit soft time limit, breaking :: Running for %.3fs', $t));
                    break;
                }
            }
        }

        // add session writer if not added
        if ($emailGatewayLogWriter instanceof EmailGatewayLogWriter &&
            !$this->logger->getWriterChain()->hasWriter($emailGatewayLogWriter)
        ) {
            $this->logger->addWriter($emailGatewayLogWriter);
        }

        // Mark account as no longer processing
        $this->markAccountAsNoLongerProcessing($account, true);

        // Close fetcher
        $fetcher->close();

        // Stop benchmark
        $endTime = microtime(true);

        // Log processing completion
        $this->logger->log(sprintf(
            'Finished processing account. Took %.2f seconds. Peak memory %.2f MB (current %.2f MB).',
            $endTime - $startTime,
            memory_get_peak_usage() / 1024 / 1024,
            memory_get_usage() / 1024 / 1024
        ), 'info');
    }

    /**
     * @return array
     */
    private function getFromHeaders()
    {
        if ($this->fromHeaders !== null) {
            return $this->fromHeaders;
        }

        $fromHeaders = explode(',', App::$container->getSetting('core_email.from_email_headers'));
        $fromHeaders = Arrays::func($fromHeaders, 'trim');
        $fromHeaders = Arrays::func($fromHeaders, 'strtolower');
        $fromHeaders = Arrays::removeFalsey($fromHeaders);

        if (!$fromHeaders) {
            $fromHeaders = ['from'];
        }

        return $fromHeaders;
    }

    /**
     * Create fetcher instance.
     *
     * @param EmailAccount $account
     *
     * @throws \InvalidArgumentException
     *
     * @return \Application\DeskPRO\EmailGateway\Fetcher\AbstractFetcher
     */
    private function createFetcher(EmailAccount $account)
    {
        // stub
        $fetcher = null;

        // get incoming account config
        $incomingAccount = $account->getIncomingAccount();

        if (!$incomingAccount instanceof AccountConfigInterface) {
            throw new \InvalidArgumentException('No incoming email account');
        }

        // Create fetcher instance
        switch ($incomingAccount->getType()) {
            case 'pop3':
            case 'office365':
                $fetcher = new Fetcher\Pop3($account, 20971520);
                break;
            case 'gmail':
                {
                    if (method_exists($incomingAccount, 'getProtocolType')) {
                        $protocolType = $incomingAccount->getProtocolType();
                        // BC, Gmail XOAUTH2 works only via IMAP
                        $fetcher = ($protocolType === GmailConfig::TYPE_OAUTH)
                            ? new Fetcher\ImapSocket($account, 20971520)
                            : new Fetcher\Pop3($account, 20971520);
                    } else {
                        $fetcher = new Fetcher\Pop3($account, 20971520);
                    }
                } break;
            case 'imap':
                $fetcher = new Fetcher\Imap($account, 20971520);
                break;
            case 'exchange':
                $fetcher = new Fetcher\Exchange($account, 20971520);
                break;
            case 'noop':
            case 'null':
                $fetcher = new Fetcher\Noop($account);
                break;
            default:
                throw new \InvalidArgumentException(
                    "Unknown incoming email account: {$account->incoming_account->getType()}"
                );
                break;
        }

        return $fetcher;
    }

    /**
     * Mark account as processing.
     *
     * @param EmailAccount $account
     *
     * @throws \Doctrine\DBAL\DBALException
     * @throws \Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException
     * @throws \Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException
     *
     * @return int
     */
    private function markAccountAsProcessing(EmailAccount $account)
    {
        // Base query, params and types
        $query  = 'UPDATE `email_accounts` SET `date_read_start` = :dt, `is_read_active` = :flag WHERE `id` = :id';
        $params = ['dt' => Carbon::now(), 'flag' => true, 'id' => $account->getId()];
        $types  = ['dt' => TYPE::DATETIME, 'flag' => TYPE::BOOLEAN, 'id' => TYPE::INTEGER];

        // Update account in database
        return App::$container->get('doctrine.dbal.default_connection')
            ->executeUpdate($query, $params, $types);
    }

    /**
     * Mark account as no longer processing.
     *
     * @param EmailAccount $account
     * @param bool         $fetcherSucceed
     *
     * @throws \Doctrine\DBAL\DBALException
     * @throws \Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException
     * @throws \Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException
     * @throws \InvalidArgumentException
     *
     * @return int
     */
    private function markAccountAsNoLongerProcessing(EmailAccount $account, $fetcherSucceed = true)
    {
        // Base query, params and types
        $query  = 'UPDATE `email_accounts` SET `is_read_active` = :flag';
        $params = ['flag' => false, 'id' => $account->getId()];
        $types  = ['flag' => TYPE::BOOLEAN, 'id' => TYPE::INTEGER];

        // If fetcher succeeded, set date_last_incoming
        if (true === $fetcherSucceed) {
            $query .= ', `date_last_incoming` = :dt';
            $params += ['dt' => Carbon::now()];
            $types += ['dt' => TYPE::DATETIME];
        }

        // Limit query to account
        $query .= ' WHERE `id` = :id';

        // Update account in database
        return App::$container->get('doctrine.dbal.default_connection')
            ->executeUpdate($query, $params, $types);
    }
}

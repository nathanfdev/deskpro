<?php

/**
 * Copyright (c) DeskPRO.
 */

/**
 * Orb.
 */

namespace Orb\Log\Writer;

use Application\DeskPRO\BlobStorage\DeskproBlobStorage;
use Application\DeskPRO\Entity\Blob;
use Application\DeskPRO\Entity\EmailAccount;
use Application\DeskPRO\Entity\EmailSource;
use Carbon\Carbon;
use DeskPRO\Bundle\AppBundle\Entity\EmailAccountLog;
use Doctrine\DBAL\Connection;
use DpSys\LowError\SystemErrorHandler;
use Orb\Log\LogItem;

/**
 * Log writer to save all email gateway proceccing logs.
 *
 * Class EmailGatewayLogWriter
 */
class EmailGatewayLogWriter extends AbstractWriter
{
    /**
     * @var Connection
     */
    protected $dbConnection;

    /**
     * @var DeskproBlobStorage
     */
    protected $dpBlobStorage;

    /**
     *  Email account.
     *
     * @var EmailAccount
     */
    protected $emailAccount;

    /**
     * EmailAccountLog record ID.
     *
     * @var null|int
     */
    protected $emailAccountLogId;

    /**
     * Log messages.
     *
     * @var array
     */
    protected $messages = [];

    /**
     * Number of fetched sources.
     *
     * @var int
     */
    protected $totalfetchedSources = 0;

    /**
     * EmailGatewayLog constructor.
     *
     * @param EmailAccount       $emailAccount
     * @param Connection         $dbConnection
     * @param DeskproBlobStorage $dpBlobStorage
     */
    public function __construct(
        EmailAccount $emailAccount,
        Connection $dbConnection,
        DeskproBlobStorage $dpBlobStorage
    ) {
        // set email account
        $this->emailAccount = $emailAccount;

        // set database connection
        $this->dbConnection = $dbConnection;

        // set blob storage
        $this->dpBlobStorage = $dpBlobStorage;

        // add simple line formatter
        $this->addFilter(new \Orb\Log\Filter\SimpleLineFormatter());
    }

    /**
     * Write a message to the log.
     *
     * @param LogItem $log_item
     */
    public function _write(LogItem $log_item)
    {
        $this->messages[] = $log_item[LogItem::MESSAGE_LINE];
    }

    /**
     * Get log enrty id. If does not exist, create new one.
     *
     * @return int
     */
    public function getLogEntryId()
    {
        if (is_null($this->emailAccountLogId)) {
            // create new log entry as we don't have entry ID yet.
            $this->dbConnection->insert(
                'email_account_logs',
                [
                    'email_account_id' => $this->emailAccount->getId(),
                    'protocol'         => $this->emailAccount->incoming_account->getType(),
                    'date_created'     => Carbon::now('UTC')->toDateTimeString(),
                ]
            );
            // get last insert id
            $this->emailAccountLogId = $this->dbConnection->lastInsertId();
        }

        return $this->emailAccountLogId;
    }

    /**
     * Set total fetched sources.
     *
     * @param int $totalFetchedSources
     *
     * @return EmailGatewayLogWriter
     */
    public function setTotalFetchedSources($totalFetchedSources = 0)
    {
        $this->totalfetchedSources = $totalFetchedSources;

        return $this;
    }

    /**
     * Increment total received emails counter.
     *
     * @return EmailGatewayLogWriter
     */
    public function incrementTotalFetchedSources()
    {
        $this->totalfetchedSources = $this->totalfetchedSources + 1;

        return $this;
    }

    /**
     * Attache email source to log entry.
     *
     * @param EmailSource $emailSource
     */
    public function attachLogEntityToEmailSource(EmailSource $emailSource)
    {
        if (!is_null($emailSource->getId())) {
            $this->dbConnection->update(
                'email_sources',
                ['email_account_log_id' => $this->getLogEntryId()],
                ['id'                   => $emailSource->getId()]
            );
        }
    }

    /**
     * Can be called manually to flush any cached entries.
     */
    public function flush()
    {
        try {
            // get entry id
            $logEntryId = $this->getLogEntryId();

            // get blob id from the log
            $logEntry = $this->dbConnection
                ->fetchAssoc(
                    'SELECT id, blob_id FROM email_account_logs WHERE id = :id',
                    ['id' => $logEntryId]
                );

            // create blob for this log entry, if it's not yet created
            $logBlobId = null;
            if (is_null($logEntry['blob_id'])) {
                $logBlobId = $this->dpBlobStorage->createBlobRecordFromString(
                    implode(PHP_EOL, $this->messages),
                    'email-gateway-runner.account-'.$this->emailAccount->getId().'.log',
                    'plain/text',
                    [
                        'prefer_gzipped' => true,
                        'tag'            => 'logs.email_gateway_runner_log',
                    ]
                )->getId();

                // update log entry
                $this->dbConnection->update(
                    'email_account_logs',
                    [
                        'blob_id'    => $logBlobId,
                        'num_emails' => $this->totalfetchedSources,
                    ],
                    ['id' => $logEntryId]
                );
            }
        } catch (\Exception $e) {
            SystemErrorHandler::logException($e);
        }
    }
}

<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\EmailGateway;

use Application\DeskPRO\App;
use Application\DeskPRO\Email\EmailAccount\EmailAccountManager;
use Application\DeskPRO\EmailGateway\Reader\AbstractReader;
use Application\DeskPRO\Entity\EmailSource;
use Orb\Log\Logger;

class RunnerExecSource
{
    /**
     * @var \Application\DeskPRO\Entity\EmailSource
     */
    private $source;

    /**
     * @var \Application\DeskPRO\Entity\EmailAccount
     */
    private $account;

    /**
     * @var Reader\AbstractReader
     */
    private $reader;

    /**
     * @var \Orb\Log\Logger
     */
    private $logger;

    /**
     * @var \Application\DeskPRO\Email\EmailAccount\EmailAccountManager
     */
    private $account_manager;

    /**
     * @var array
     */
    private $from_headers = ['from'];

    /**
     * @param EmailSource         $source
     * @param AbstractReader      $reader
     * @param EmailAccountManager $account_manager
     * @param Logger              $logger
     */
    public function __construct(EmailSource $source, AbstractReader $reader = null, EmailAccountManager $account_manager, Logger $logger = null)
    {
        $this->source          = $source;
        $this->account         = $source->getEmailAccount();
        $this->reader          = $reader;
        $this->logger          = $logger ?: new Logger();
        $this->account_manager = $account_manager;
    }

    /**
     * @param array $from_headers
     */
    public function setFromHeaders(array $from_headers)
    {
        $this->from_headers = $from_headers;
    }

    /**
     * @return AbstractReader
     */
    private function getReader()
    {
        if (!$this->reader) {
            $this->logger->logDebug('No reader set, creating it from raw source');
            $ts           = microtime(true);
            $this->reader = App::getContainer()->getEmailEzcReaderFactory()->create();
            $this->reader->setRawSource($this->source['raw_source']);
            $this->logger->logDebug(sprintf('Reader created in %.3fs', microtime(true) - $ts));
        }

        if (!$this->reader->hasProperty('email_source')) {
            $this->reader->setProperty('email_source', $this->source);
        }

        return $this->reader;
    }

    /**
     * @return array
     */
    public function run()
    {
        try {
            $reader = $this->getReader();
        } catch (\Exception $e) {
            $this->logger->logDebug('Exception while decoding: '.$e->getMessage());

            return [
                'status'     => 'rejected',
                'error_code' => 'message_missing',
            ];
        }

        //------------------------------
        // Save proper header values
        //------------------------------

        if ($h = $reader->getHeader('To')) {
            $this->source->header_to = implode(', ', $h->getAllParts());
        }
        if ($h = $reader->getHeader('Cc')) {
            $this->source->header_cc = implode(', ', $h->getAllParts());
        }
        if ($h = $reader->getHeader('Subject')) {
            $this->source->header_subject = implode(', ', $h->getAllParts());
        }
        if ($h = $reader->getHeader('From')) {
            $this->source->header_from = implode(', ', $h->getAllParts());
        }

        if ($reader->getRealFromAddress()) {
            $this->source->from_email = $reader->getRealFromAddress()->getEmail() ?: '';
        } else {
            $this->source->from_email = '';
        }

        //------------------------------
        // Output debug TO
        //------------------------------

        $to = [];
        foreach ($reader->getToAddresses() as $x) {
            $to[] = $x->getEmail();
        }
        $to = implode(', ', $to);

        $from = $reader->getFromAddress()->getEmail();

        $subj = substr($reader->getSubject()->getSubject(), 0, 40);
        $this->logger->logDebug("[Message] To: $to :: From: $from :: Subject: $subj");

        //------------------------------
        // Output debug FROM
        //------------------------------

        $from_headers = $this->from_headers;
        if ($from_headers) {
            $this->logger->logDebug(sprintf('From header priority: %s', implode(', ', $from_headers)));
            $reader->setFromHeaderPriority($from_headers);
            $from = $reader->getFromAddress()->getEmail();
            $this->logger->logDebug(sprintf('[Message] Using From: %s', $from));
        }

        //------------------------------
        // Run preprocessor
        //------------------------------

        $this->logger->logDebug('Running preprocessor');
        $result = $this->runPreProcessor();

        if ($result['status'] != 'okay') {
            $this->logger->logWarn(sprintf('--> Preprocessor: ', $result['status'], @$result['error_code']));

            return $result;
        }

        $this->logger->logDebug('--> Preprocessor OKAY');

        //------------------------------
        // Run email processor
        //------------------------------

        $this->logger->logDebug('Running email processor');
        $result = $this->runEmailProcessor();

        if ($result['status'] != 'okay') {
            $this->logger->logWarn(sprintf('--> Email processor: ', $result['status'], @$result['error_code']));

            return $result;
        }

        $this->logger->logDebug('--> Email processor OKAY');
        $this->logger->logDebug(sprintf('--> Created: %s %s', @$result['created_object_type'], @$result['created_object_id']));

        return $result;
    }

    /**
     * @return array
     */
    private function runPreProcessor()
    {
        $pre_processor = new PreProcessor($this->account, $this->getReader(), ['logger' => $this->logger]);
        $pre_processor->run();

        if ($pre_processor->isValid()) {
            return [
                'status' => 'okay',
            ];
        } else {
            return [
                'status'     => $pre_processor->getErrorType() ?: 'error',
                'error_code' => $pre_processor->getErrorCode(),
            ];
        }
    }

    /**
     * @return array
     */
    private function runEmailProcessor()
    {
        $proc = $this->account_manager->getEmailProcessor(
            $this->account,
            $this->getReader(),
            ['logger' => $this->logger, 'email_source' => $this->source]
        );
        if (!$proc) {
            return [
                'status'     => 'rejected',
                'error_code' => 'invalid_address',
            ];
        }

        $proc->run();

        if ($proc->isValid()) {
            return [
                'status'              => 'okay',
                'created_object_type' => $proc->getCreatedObjectType(),
                'created_object_id'   => $proc->getCreatedObjectId(),
                'created_object_info' => $proc->getCreatedObjectInfo(),
            ];
        } else {
            return [
                'status'     => $proc->getErrorType() ?: 'error',
                'error_code' => $proc->getErrorCode(),
            ];
        }
    }
}

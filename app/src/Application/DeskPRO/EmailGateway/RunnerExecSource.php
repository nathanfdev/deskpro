<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/  |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2014, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at https://www.deskpro.com/eula/                            |
|                                                                          |
| By using this software, you acknowledge having read the license          |
| and agree to be bound thereby.                                           |
|                                                                          |
| Please note that DeskPRO is not free software. We release the full       |
| source code for our software because we trust our users to pay us for    |
| the huge investment in time and energy that has gone into both creating  |
| this software and supporting our customers. By providing the source code |
| we preserve our customers' ability to modify, audit and learn from our   |
| work. We have been developing DeskPRO since 2001, please help us make it |
| another decade.                                                          |
|                                                                          |
| Like the work you see? Think you could make it better? We are always     |
| looking for great developers to join us: http://www.deskpro.com/jobs/    |
|                                                                          |
| ~ Thanks, Everyone at Team DeskPRO                                       |
\**************************************************************************/

/**
 * DeskPRO
 *
 * @package DeskPRO
 */

namespace Application\DeskPRO\EmailGateway;

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
    private $from_headers = array('from');


    /**
     * @param EmailSource         $source
     * @param AbstractReader      $reader
     * @param EmailAccountManager $account_manager
     * @param Logger              $logger
     */
    public function __construct(EmailSource $source, AbstractReader $reader = null, EmailAccountManager $account_manager, Logger $logger = null)
    {
        $this->source  = $source;
        $this->account = $source->email_account;
        $this->reader  = $reader;
        $this->logger  = $logger ?: new Logger();
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
            $this->reader = new \Application\DeskPRO\EmailGateway\Reader\EzcReader();
            $this->reader->setRawSource($this->source['raw_source']);
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
            $this->logger->logDebug("Exception while decoding: " . $e->getMessage());

            return array(
                'status'     => 'rejected',
                'error_code' => 'message_missing',
            );
        }

        #------------------------------
        # Output debug TO
        #------------------------------

        $to = array();
        foreach ($reader->getToAddresses() as $x) {
            $to[] = $x->getEmail();
        }
        $to = implode(', ', $to);

        $from = $reader->getFromAddress()->getEmail();

        $subj = substr($reader->getSubject()->getSubject(), 0, 40);
        $this->logger->logDebug("[Message] To: $to :: From: $from :: Subject: $subj");

        #------------------------------
        # Output debug FROM
        #------------------------------

        $from_headers = $this->from_headers;
        if ($from_headers) {
            $this->logger->logDebug(sprintf("From header priority: %s", implode(', ', $from_headers)));
            $reader->setFromHeaderPriority($from_headers);
            $from = $reader->getFromAddress()->getEmail();
            $this->logger->logDebug(sprintf("[Message] Using From: %s", $from));
        }

        #------------------------------
        # Run preprocessor
        #------------------------------

        $this->logger->logDebug("Running preprocessor");
        $result = $this->runPreProcessor();

        if ($result['status'] != 'okay') {
            $this->logger->logWarn(sprintf('--> Preprocessor: ', $result['status'], @$result['error_code']));

            return $result;
        }

        $this->logger->logDebug("--> Preprocessor OKAY");

        #------------------------------
        # Run email processor
        #------------------------------

        $this->logger->logDebug("Running email processor");
        $result = $this->runEmailProcessor();

        if ($result['status'] != 'okay') {
            $this->logger->logWarn(sprintf('--> Email processor: ', $result['status'], @$result['error_code']));

            return $result;
        }

        $this->logger->logDebug("--> Email processor OKAY");
        $this->logger->logDebug(sprintf("--> Created: %s %s", @$result['created_object_type'], @$result['created_object_id']));

        return $result;
    }


    /**
     * @return array
     */
    private function runPreProcessor()
    {
        $pre_processor = new PreProcessor($this->account, $this->getReader(), array('logger' => $this->logger));
        $pre_processor->run();

        if ($pre_processor->isValid()) {
            return array(
                'status' => 'okay'
            );
        } else {
            return array(
                'status' => $pre_processor->getErrorType() ?: 'error',
                'error_code' => $pre_processor->getErrorCode()
            );
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
            array('logger' => $this->logger)
        );
        if (!$proc) {
            return array(
                'status' => 'rejected',
                'error_code' => 'invalid_address'
            );
        }

        $proc->run();

        if ($proc->isValid()) {
            return array(
                'status' => 'okay',
                'created_object_type' => $proc->getCreatedObjectType(),
                'created_object_id'   => $proc->getCreatedObjectId()
            );
        } else {
            return array(
                'status' => $proc->getErrorType() ?: 'error',
                'error_code' => $proc->getErrorCode()
            );
        }
    }
}

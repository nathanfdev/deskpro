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
 * @category EmailGateway
 */

namespace Application\DeskPRO\EmailGateway\TicketGateway;

use Application\DeskPRO\App;
use Application\DeskPRO\EmailGateway\InlineImageTokens;
use Application\DeskPRO\EmailGateway\PersonFromEmailProcessor;
use Orb\Log\Logger;
use Orb\Util\Strings;
use Orb\Validator\StringEmail;

abstract class ProcessAbstract
{
    /**
     * @var \Orb\Log\Logger
     */
    protected $logger;

    /**
     * @var string
     */
    protected $error = null;

    /**
     * @var string
     */
    protected $error_type = null;

    /**
     * Indexed by blob id
     * @var \Application\DeskPRO\Entity\Blob[]
     */
    protected $processed_blobs = null;

    /**
     * Same as processed_blobs except indexed by Content-ID
     *
     * @var \Application\DeskPRO\Entity\Blob[]
     */
    protected $processed_blobs_cid = array();

    /**
     * @var array
     */
    protected $inline_blobs = array();

    /**
     * @var array
     */
    protected $dupe_inline_blobs = array();

    /**
     * @var \Application\DeskPRO\Entity\Person
     */
    protected $person;

    /**
     * @var \Application\DeskPRO\EmailGateway\Reader\AbstractReader
     */
    protected $reader;

    /**
     * @var \Application\DeskPRO\Translate\Translate
     */
    protected $translator;

    /**
     * @return mixed
     */
    abstract public function run();

    /**
     * Set the logger
     * @param \Orb\Log\Logger $logger
     */
    public function setLogger(Logger $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @return \Orb\Log\Logger
     */
    public function getLogger()
    {
        if (!$this->logger) {
            $this->logger = new Logger();
        }

        return $this->logger;
    }

    /**
     * @param string $message
     * @param string $pri
     */
    protected function logMessage($message, $pri = 'debug')
    {
        if ($this->logger) {
            $this->logger->log($message, $pri);
        }
    }

    /**
     * @param string $error
     * @param string $error_type
     */
    protected function setError($error, $error_type = 'rejected')
    {
        $this->error      = $error;
        $this->error_type = $error_type;
    }

    /**
     * @return string
     */
    public function getError()
    {
        return $this->error;
    }

    /**
     * @return string
     */
    public function getErrorType()
    {
        return $this->error_type;
    }

    /**
     * @return \Application\DeskPRO\Tickets\TicketManager
     */
    protected function getTicketManager()
    {
        return App::getSystemService('ticket_manager');
    }

    public function handleCc($ticket, array $ccs)
    {
        $account_manager = App::$container->getEmailAccountManager();
        $db              = App::$container->getDb();

        $count = 0;
        foreach ($ccs as $cc) {
            $cc_email = $cc->getEmail();
            $this->logMessage("Checking cc: $cc_email");

            // Max 10 CC's to prevent mass spamming
            if ($count >= 10) {
                $this->logMessage("CC limit reached, break");
                break;
            }

            // Make sure its actually valid
            if (!StringEmail::isValueValid($cc_email)) {
                $this->logMessage("Invalid email address");
                continue;
            }

            if ($account_manager->findAccountForEmailAddress($cc_email)) {
                $this->logMessage("Skipping cc: $cc_email (matches helpdesk account address)");
                continue;
            }

            if ($ticket->hasParticipantEmailAddress($cc_email)) {
                $this->logMessage("Skipping cc: $cc_email (address already on ticket)");
                continue;
            }

            $person_processor = new PersonFromEmailProcessor();

            $cc_person = $person_processor->findPerson($cc);
            if (!$cc_person) {
                // Closed helpdesk and an unknown CC means we drop it
                if (!App::getContainer()->getSetting('core.reg_enabled')) {
                    $this->logMessage("Skipping cc: $cc_email (no person match and closed helpdesk)");
                    continue;
                }

                $db->beginTransaction();
                $cc_person = $person_processor->createPerson($cc, true);
                $this->logMessage("Added cc: $cc_email (Person {$cc_person->id})");
                $db->commit();
            }

            if ($cc_person) {
                if ($cc_person->is_agent && !$this->person->is_agent) {
                    if (!$this->person || !$this->person->getId() || !$this->person->is_agent) {
                        if (!App::getSetting('core_tickets.add_agent_ccs')) {
                            $this->logMessage("Skipping agent CC because core_tickets.add_agent_ccs is off");
                            continue;
                        }
                    }
                }

                $this->logMessage("Add CC person: {$cc_person->getId()}");

                if (!$ticket->hasParticipantPerson($cc_person)) {
                    $ticket->addParticipantPerson($cc_person);
                    $count++;
                }
            }
        }
    }

    /**
     * Process all attachments on the email into temp blobs.
     *
     * @return \Application\DeskPRO\Entity\Blob[]
     */
    protected function processBlobs($skip_attach = null)
    {
        if ($this->processed_blobs !== null) {
            return $this->processed_blobs;
        }
        $this->processed_blobs = array();

        $accept = App::$container->getAttachmentAccepter();
        $r_set  = $accept->getRestrictionSet($this->person->is_agent ? 'emails.agent' : 'emails.user');

        foreach ($this->reader->getAttachments() as $attach) {
            if ($skip_attach && $skip_attach === $attach) {
                continue;
            }

            $props = array(
                'size' => strlen($attach->getFileContents()),
                'ext'  => Strings::getExtension($attach->getFileName()),
            );

            $error = $r_set->getErrorForProperties($props);
            if ($error) {
                $this->logMessage(sprintf("[processBlobs] %s rejected: %s %s", $attach->getFileName(), $error['error_code'], $error['error_detail']));
                continue;
            }

            $blob = App::getContainer()->getBlobStorage()->createBlobRecordFromString(
                $attach->getFileContents(),
                $attach->getFileName(),
                $attach->getMimeType()
            );

            $this->logMessage(sprintf("Processed blob %s (%d)", $blob->filename, $blob->id));
            $this->processed_blobs[$blob->id] = $blob;

            if ($attach->getContentId()) {
                $this->processed_blobs_cid[$attach->getContentId()] = $blob;
            }
        }

        return $this->processed_blobs;
    }

    /**
     * @param  string            $body
     * @param  InlineImageTokens $inline_images
     * @return string
     */
    public function replaceInlineAttachTokens($body, InlineImageTokens $inline_images)
    {
        $exist_inline_blobs = array();

        if (isset($this->ticket)) {
            $blob_hashes = array();

            foreach ($this->processed_blobs as $blob) {
                $blob_hashes[] = $blob->blob_hash;
            }

            if ($blob_hashes) {
                $exist_attach = App::getOrm()->createQuery("
                    SELECT a, b
                    FROM DeskPRO:TicketAttachment a
                    LEFT JOIN a.blob b
                    WHERE a.ticket = ?0 AND b.blob_hash IN (?1)
                ")->execute(array($this->ticket, $blob_hashes));

                foreach ($exist_attach as $a) {
                    $exist_inline_blobs[$a->blob->blob_hash] = $a->blob;
                }
            }
        }

        foreach ($inline_images->getCids() as $cid) {
            if (!isset($this->processed_blobs_cid[$cid])) {
                continue;
            }

            $blob = $this->processed_blobs_cid[$cid];

            // If this ticket already has a blob like this,
            // then mark it as a dupe and rewrite the inline reference
            // to the one we've already saved
            if (isset($exist_inline_blobs[$blob->blob_hash])) {
                $this->logMessage(sprintf("Duplicate inline blob %s is being discarded, existing blob %s will be used", $blob->getFilenameSafe(), $blob->getId()));
                $this->dupe_inline_blobs[$blob->getId()] = $blob;
                $blob                                    = $exist_inline_blobs[$blob->blob_hash];
            }

            if ($blob->isImage()) {
                $this->inline_blobs[$blob->getId()] = $blob;
                $replace                            = '[attach:image:'.$blob->getAuthId().':'.$blob->getFilenameSafe().']';
            } else {
                $replace = '[attach:file:'.$blob->getAuthId().':'.$blob->getFilenameSafe().']';
            }

            $body = $inline_images->replaceToken($cid, $replace, $body);
        }

        return $body;
    }
}

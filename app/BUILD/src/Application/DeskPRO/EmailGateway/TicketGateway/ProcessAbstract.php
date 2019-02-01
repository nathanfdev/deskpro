<?php

/**
 * DeskPRO.
 *
 * @category EmailGateway
 */

namespace Application\DeskPRO\EmailGateway\TicketGateway;

use Application\DeskPRO\App;
use Application\DeskPRO\EmailGateway\InlineImageTokens;
use Application\DeskPRO\EmailGateway\PersonFromEmailProcessor;
use Orb\Log\Logger;
use Orb\Util\Strings;
use Orb\Validator\StringEmail;
use Symfony\Component\HttpFoundation\File\MimeType\ExtensionGuesser;

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
     * Indexed by blob id.
     *
     * @var \Application\DeskPRO\Entity\Blob[]
     */
    protected $processed_blobs = null;

    /**
     * Same as processed_blobs except indexed by Content-ID.
     *
     * @var \Application\DeskPRO\Entity\Blob[]
     */
    protected $processed_blobs_cid = [];

    /**
     * @var array
     */
    protected $inline_blobs = [];

    /**
     * @var array
     */
    protected $dupe_inline_blobs = [];

    /**
     * @var \Application\DeskPRO\Entity\Person
     */
    protected $person;

    /**
     * @var \Application\DeskPRO\Entity\EmailAccount
     */
    protected $account;

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
     * Set the logger.
     *
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
        $max_cc          = (int) App::getSetting('core_tickets.email_cc_max_count');
        $max_cc          = $max_cc ?: 100;

        $count = 0;
        foreach ($ccs as $cc) {
            $cc_email = $cc->getEmail();
            $this->logMessage("Checking cc: $cc_email");

            // Max 100 CC's to prevent mass spamming
            if ($count >= $max_cc) {
                $this->logMessage("CC limit ({$max_cc}) reached, break");
                break;
            }

            // Make sure its actually valid
            if (!StringEmail::isValueValid($cc_email)) {
                $this->logMessage('Invalid email address');
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
                if (!$person_processor->canAssociatePersonWithAccountBrands($this->account)) {
                    $this->logMessage("Skipping cc: $cc_email (no person match and closed helpdesk)");
                    continue;
                }

                $db->beginTransaction();
                $cc_person = $person_processor->createPerson($cc);
                $this->logMessage("Added cc: $cc_email (Person {$cc_person->id})");
                $brand = $person_processor->associatePersonWithAccountBrand($this->account, $cc_person);
                if ($brand) {
                    $this->logMessage("Add Person #{$cc_person->id} to Account Brand #{$brand->id}");
                } else {
                    $this->logMessage("WARNING. Can't find Brand for Account (#{$this->account->id}), but should");
                }
                $db->commit();
            } elseif (!$person_processor->isPersonAssociatedWithAccountBrands($this->account, $cc_person)) {
                $brand = $person_processor->associatePersonWithAccountBrand($this->account, $cc_person);
                if ($brand) {
                    $this->logMessage("Add Person #{$cc_person->id} to Account Brand #{$brand->id}");
                } else {
                    $this->logMessage("Skipping cc: $cc_email (person not associated with account brands and closed helpdesk)");
                    continue;
                }
            }

            if ($cc_person) {
                if ($cc_person->is_agent && !$this->person->is_agent) {
                    if (!$this->person || !$this->person->getId() || !$this->person->is_agent) {
                        if (!App::getSetting('core_tickets.add_agent_ccs')) {
                            $this->logMessage('Skipping agent CC because core_tickets.add_agent_ccs is off');
                            continue;
                        }
                    }
                }

                $this->logMessage("Add CC person: {$cc_person->getId()}");

                if (!$ticket->hasParticipantPerson($cc_person)) {
                    $ticket->addParticipantPerson($cc_person);
                    ++$count;
                }
            }
        }
    }

    /**
     * Process all attachments on the email into temp blobs.
     *
     * @return \Application\DeskPRO\Entity\Blob[]
     */
    public function processBlobs($skip_attach = null)
    {
        if ($this->processed_blobs !== null) {
            return $this->processed_blobs;
        }
        $this->processed_blobs = [];

        $accept = App::$container->getAttachmentAccepter();
        $r_set  = $accept->getRestrictionSet($this->person->is_agent ? 'emails.agent' : 'emails.user');

        foreach ($this->reader->getAttachments() as $attach) {
            if ($skip_attach && $skip_attach === $attach) {
                continue;
            }

            $props = [
                'size' => strlen($attach->getFileContents()),
                'ext'  => Strings::getExtension($attach->getFileName()),
            ];

            $error = $r_set->getErrorForProperties($props);
            if ($error) {
                $this->logMessage(sprintf('[processBlobs] %s rejected: %s %s', $attach->getFileName(), $error['error_code'], $error['error_detail']));
                continue;
            }

            $currentFilename = $attach->getFileName();
            $guesser         = ExtensionGuesser::getInstance();
            $guessedExt      = $guesser->guess($attach->getMimeType());
            $parts           = explode('.', $currentFilename, 2);
            if (!isset($parts[1]) && $guessedExt) {
                $filename = $currentFilename.'.'.$guessedExt;
            } else {
                $filename = $currentFilename;
            }

            $blob = App::getContainer()->getBlobStorage()->createBlobRecordFromString(
                $attach->getFileContents(),
                $filename,
                $attach->getMimeType(),
                ['tag' => $attach->is_inline ? null : 'ticket_attachment']
            );

            $this->logMessage(sprintf('Processed blob %s (%d)', $blob->filename, $blob->id));
            $this->processed_blobs[$blob->id] = $blob;

            if ($attach->getContentId()) {
                $this->processed_blobs_cid[$attach->getContentId()] = $blob;
            }
        }

        return $this->processed_blobs;
    }

    /**
     * @param string            $body
     * @param InlineImageTokens $inline_images
     *
     * @return string
     */
    public function replaceInlineAttachTokens($body, InlineImageTokens $inline_images)
    {
        $exist_inline_blobs = [];

        if (isset($this->ticket)) {
            $blob_hashes = [];

            foreach ($this->processed_blobs as $blob) {
                $blob_hashes[] = $blob->blob_hash;
            }

            if ($blob_hashes) {
                $exist_attach = App::getOrm()->createQuery('
                    SELECT a, b
                    FROM DeskPRO:TicketAttachment a
                    LEFT JOIN a.blob b
                    WHERE a.ticket = ?0 AND b.blob_hash IN (?1)
                ')->execute([$this->ticket, $blob_hashes]);

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
                $this->logMessage(sprintf('Duplicate inline blob %s is being discarded, existing blob %s will be used', $blob->getFilenameSafe(), $blob->getId()));
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

<?php



namespace Application\DeskPRO\EmailGateway\TicketGateway;

use Application\DeskPRO\App;
use Application\DeskPRO\BlobStorage\BlobStorageException;
use Application\DeskPRO\BlobStorage\DeskproBlobStorage;
use Application\DeskPRO\EmailGateway\InlineImageTokens;
use Application\DeskPRO\EmailGateway\PersonFromEmailProcessor;
use Application\DeskPRO\EmailGateway\Reader\AbstractReader;
use Application\DeskPRO\Entity\Blob;
use Application\DeskPRO\Entity\EmailAccount;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Tickets\TicketManager;
use Application\DeskPRO\Translate\Translate;
use Doctrine\DBAL\ConnectionException;
use Doctrine\DBAL\Exception\InvalidArgumentException;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\ORM\TransactionRequiredException;
use Orb\Log\Logger;
use Orb\Util\Strings;
use Orb\Validator\StringEmail;
use Symfony\Component\HttpFoundation\File\MimeType\ExtensionGuesser;

abstract class ProcessAbstract
{
    /**
     * @var Logger
     */
    protected $logger;

    /**
     * @var string
     */
    protected $error = null;

    /**
     * @var string
     */
    protected $errorType = null;

    /**
     * Indexed by blob id.
     *
     * @var Blob[]
     */
    protected $processedBlobs = null;

    /**
     * Same as processed_blobs except indexed by Content-ID.
     *
     * @var Blob[]
     */
    protected $processedBlobsCid = [];

    /**
     * @var array
     */
    protected $inlineBlobs = [];

    /**
     * @var array
     */
    protected $dupeInlineBlobs = [];

    /**
     * @var Person
     */
    protected $person;

    /**
     * @var EmailAccount
     */
    protected $account;

    /**
     * @var AbstractReader
     */
    protected $reader;

    /**
     * @var Translate
     */
    protected $translator;

    /**
     * @return mixed
     */
    abstract public function run();

    /**
     * Set the logger.
     *
     * @param Logger $logger
     */
    public function setLogger(Logger $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @return Logger
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
     * @param string $errorType
     */
    protected function setError($error, $errorType = 'rejected')
    {
        $this->error     = $error;
        $this->errorType = $errorType;
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
        return $this->errorType;
    }

    /**
     * @return TicketManager
     */
    protected function getTicketManager()
    {
        return App::getSystemService('ticket_manager');
    }

    /**
     * @param Ticket $ticket
     * @param array  $ccs
     *
     * @throws ConnectionException
     */
    public function handleCc($ticket, array $ccs)
    {
        $accountManager = App::$container->getEmailAccountManager();
        $db             = App::$container->getDb();
        $maxCc          = (int) App::getSetting('core_tickets.email_cc_max_count');
        $maxCc          = $maxCc ?: 100;

        $removed   = [];
        $reAddedCC = false;
        if ($ticket->getAttribute('removed_ccs')) {
            $removed = json_decode($ticket->getAttribute('removed_ccs')->getValue(), true);
        }

        $count = 0;
        foreach ($ccs as $cc) {
            $ccEmail = $cc->getEmail();
            $this->logMessage("Checking cc: $ccEmail");

            // Max 100 CC's to prevent mass spamming
            if ($count >= $maxCc) {
                $this->logMessage("CC limit ({$maxCc}) reached, break");

                break;
            }

            // Make sure its actually valid
            if (!StringEmail::isValueValid($ccEmail)) {
                $this->logMessage('Invalid email address');

                continue;
            }

            if ($accountManager->findAccountForEmailAddress($ccEmail)) {
                $this->logMessage("Skipping cc: $ccEmail (matches helpdesk account address)");

                continue;
            }

            if ($ticket->hasParticipantEmailAddress($ccEmail)) {
                $this->logMessage("Skipping cc: $ccEmail (address already on ticket)");

                continue;
            }

            $key = array_search($ccEmail, $removed);
            if ($key !== false) {
                unset($removed[$key]);
                $reAddedCC = true;
            }

            $personProcessor = new PersonFromEmailProcessor();

            $ccPerson = $personProcessor->findPerson($cc);
            if (!$ccPerson) {
                // Closed helpdesk and an unknown CC means we drop it
                if (!$personProcessor->canAssociatePersonWithAccountBrands($this->account)) {
                    $this->logMessage("Skipping cc: $ccEmail (no person match and closed helpdesk)");

                    continue;
                }

                $db->beginTransaction();
                $ccPerson = $personProcessor->createPerson($cc);
                $this->logMessage("Added cc: $ccEmail (Person {$ccPerson->id})");
                $brand = $personProcessor->associatePersonWithAccountBrand($this->account, $ccPerson);
                if ($brand) {
                    $this->logMessage("Add Person #{$ccPerson->id} to Account Brand #{$brand->id}");
                } else {
                    $this->logMessage("WARNING. Can't find Brand for Account (#{$this->account->id}), but should");
                }
                $db->commit();
            } elseif (!$personProcessor->isPersonAssociatedWithAccountBrands($this->account, $ccPerson)) {
                $brand = $personProcessor->associatePersonWithAccountBrand($this->account, $ccPerson);
                if ($brand) {
                    $this->logMessage("Add Person #{$ccPerson->id} to Account Brand #{$brand->id}");
                } else {
                    $this->logMessage("Skipping cc: $ccEmail (person not associated with account brands and closed helpdesk)");

                    continue;
                }
            }

            if ($ccPerson) {
                if ($ccPerson->is_agent) {
                    if (!App::getSetting('core_tickets.add_agent_ccs')) {
                        $this->logMessage('Skipping agent CC because core_tickets.add_agent_ccs is off');

                        continue;
                    }
                }

                $this->logMessage("Add CC person: {$ccPerson->getId()}");

                if (!$ticket->hasParticipantPerson($ccPerson)) {
                    $ticket->addParticipantPerson($ccPerson);
                    ++$count;
                }
            }
        }
        if ($reAddedCC) {
            $removedCCs = $ticket->getAttribute('removed_ccs');
            $removedCCs->setValue(json_encode($removed));
            $ticket->addAttribute($removedCCs);
        }
    }

    /**
     * Process all attachments on the email into temp blobs.
     *
     * @param null $skipAttach
     *
     * @throws BlobStorageException
     * @throws InvalidArgumentException
     * @throws ORMException
     * @throws OptimisticLockException
     * @throws TransactionRequiredException
     *
     * @return Blob[]
     */
    public function processBlobs($skipAttach = null)
    {
        if ($this->processedBlobs !== null) {
            return $this->processedBlobs;
        }
        $this->processedBlobs = [];

        $accept = App::$container->getAttachmentAccepter();
        $rSet   = $accept->getRestrictionSet($this->person->is_agent ? 'emails.agent' : 'emails.user');

        foreach ($this->reader->getAttachments() as $attach) {
            if ($skipAttach && $skipAttach === $attach) {
                continue;
            }

            $props = [
                'size' => strlen($attach->getFileContents()),
                'ext'  => Strings::getExtension($attach->getFileName()),
            ];

            $error = $rSet->getErrorForProperties($props);
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
                ['tag' => $attach->is_inline ? null : DeskproBlobStorage::TAG_TICKET_ATTACHMENT]
            );

            $this->logMessage(sprintf('Processed blob %s (%d)', $blob->filename, $blob->id));
            $this->processedBlobs[$blob->id] = $blob;

            if ($attach->getContentId()) {
                $this->processedBlobsCid[$attach->getContentId()] = $blob;
            }
        }

        return $this->processedBlobs;
    }

    /**
     * @param string            $body
     * @param InlineImageTokens $inlineImages
     *
     * @return string
     */
    public function replaceInlineAttachTokens($body, InlineImageTokens $inlineImages)
    {
        $existInlineBlobs = [];

        if (isset($this->ticket)) {
            $blobHashes = [];

            foreach ($this->processedBlobs as $blob) {
                $blobHashes[] = $blob->blob_hash;
            }

            if ($blobHashes) {
                $existAttach = App::getOrm()->createQuery('
                    SELECT a, b
                    FROM DeskPRO:TicketAttachment a
                    LEFT JOIN a.blob b
                    WHERE a.ticket = ?0 AND b.blob_hash IN (?1)
                ')->execute([$this->ticket, $blobHashes]);

                foreach ($existAttach as $a) {
                    $existInlineBlobs[$a->blob->blob_hash] = $a->blob;
                }
            }
        }

        foreach ($inlineImages->getCids() as $cid) {
            if (!isset($this->processedBlobsCid[$cid])) {
                continue;
            }

            $blob = $this->processedBlobsCid[$cid];

            // If this ticket already has a blob like this,
            // then mark it as a dupe and rewrite the inline reference
            // to the one we've already saved
            if (isset($existInlineBlobs[$blob->blob_hash])) {
                $this->logMessage(sprintf('Duplicate inline blob %s is being discarded, existing blob %s will be used', $blob->getFilenameSafe(), $blob->getId()));
                $this->dupeInlineBlobs[$blob->getId()] = $blob;
                $blob                                  = $existInlineBlobs[$blob->blob_hash];
            }

            if ($blob->isImage()) {
                $this->inlineBlobs[$blob->getId()] = $blob;
                $replace                           = '[attach:image:'.$blob->getAuthId().':'.$blob->getFilenameSafe().']';
            } else {
                $replace = '[attach:file:'.$blob->getAuthId().':'.$blob->getFilenameSafe().']';
            }

            $body = $inlineImages->replaceToken($cid, $replace, $body);
        }

        return $body;
    }
}

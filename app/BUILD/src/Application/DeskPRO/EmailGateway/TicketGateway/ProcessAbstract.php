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
use DeskPRO\Bundle\AppBundle\Util\HttpClient;
use DpSys\LowError\SystemErrorHandler;
use GuzzleHttp;
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
    protected function processBlobs($skip_attach = null)
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
                $attach->getMimeType()
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

    protected function importReplaceLinkedImages($body)
    {
        if (!App::getContainer()->getSetting('core.emails.download_hotlinked_images.enabled')) {
            $this->logMessage('Skipping `importReplaceLinkedImages` because settings '
                              .'`core.emails.download_hotlinked_images.enabled` disabled');

            return $body;
        }

        static $cache;
        $m      = null;
        $tmpDir = App::$container->get('deskpro.app_env')->getUserTmpDir();
        $client = new HttpClient([
            GuzzleHttp\RequestOptions::ALLOW_REDIRECTS => true,
            GuzzleHttp\RequestOptions::CONNECT_TIMEOUT => 4,
            GuzzleHttp\RequestOptions::TIMEOUT         => 10,
        ]);
        $maxImageSize = (int) App::getContainer()->getSetting('core.emails.download_hotlinked_images.image_maxsize');
        $maxTotalSize = (int) App::getContainer()->getSetting('core.emails.download_hotlinked_images.total_maxsize');

        $totalImageSize = 0;
        if (preg_match_all('#(<|&lt;)img[^>]*/?(>|&gt;)((<|&lt;)/img(>|&gt;))?#iu', $body, $m, \PREG_SET_ORDER)) {
            foreach ($m as $match) {
                // Check if it is even an inline image
                $src = Strings::extractRegexMatch('#src=("|\')((https?:)?//.*?)(\1)#iu', $match[0], 2);
                if ($src) {
                    if (isset($cache[$src])) {
                        $body = str_replace($match[0], $cache[$src], $body);
                    } else {
                        $tmpFile  = $tmpDir.'/email-image-'.mt_rand(1000, 9999);
                        $resource = fopen($tmpFile, 'w');
                        $errors   = [];
                        SystemErrorHandler::runWithoutErrorHandler(function () use ($client, $src, $resource) {
                            $client->request('GET', $src, ['sink' => $resource]);
                        }, $errors);
                        if ($errors) {
                            $e = $errors[0];
                            $this->logger->logError(sprintf('Download file failed: [%s:%s] %s', $e['Type'], $e['code'], substr($e['message'], 0, 1000)));
                            $tag  = "<a href=\"$src\" target=\"_blank\">$src</a>";
                            $body = str_replace($match[0], $tag, $body);

                            @fclose($resource);
                            continue;
                        }
                        @fclose($resource);
                        if (!file_exists($tmpFile)) {
                            continue;
                        }
                        $imageSize = filesize($tmpFile);
                        // We don't import images over 10 MB and more than 25MB of images in total
                        if ($imageSize > $maxImageSize || $totalImageSize + $imageSize > $maxTotalSize) {
                            unlink($tmpFile);
                            $tag  = "<a href=\"$src\" target=\"_blank\">$src</a>";
                            $body = str_replace($match[0], $tag, $body);
                            continue;
                        }
                        $totalImageSize += $imageSize;
                        if (function_exists('exif_imagetype')) {
                            if (!$type = @exif_imagetype($tmpFile)) {
                                // The downloaded file is not an image
                                unlink($tmpFile);
                                continue;
                            }
                        } else {
                            if (!$size = @getimagesize($tmpFile)) {
                                // The downloaded file is not an image
                                unlink($tmpFile);
                                continue;
                            }
                            $type = $size[2];
                        }

                        $name = $this->generateNameFromType($type);
                        $blob = App::getContainer()->getBlobStorage()->createBlobRecordFromFile(
                            $tmpFile,
                            $name[0],
                            $name[1]
                        );
                        unlink($tmpFile);
                        $tag         = '[attach:image:'.$blob->getAuthcode().':'.$name[0].']';
                        $body        = str_replace($match[0], $tag, $body);
                        $cache[$src] = $tag;
                    }
                }
            }
        }

        return $body;
    }

    protected function generateNameFromType($type)
    {
        $name = 'image'.mt_rand(1000, 9999);
        switch ($type) {
            case IMAGETYPE_GIF:
                return [$name.'.gif', 'image/gif'];
            case IMAGETYPE_JPEG:
                return [$name.'.jpg', 'image/jpeg'];
            case IMAGETYPE_PNG:
                return [$name.'.png', 'image/png'];
            case IMAGETYPE_BMP:
                return [$name.'.bmp', 'image/bmp'];
            case IMAGETYPE_TIFF_II:
            case IMAGETYPE_TIFF_MM:
                return [$name.'.tiff', 'image/tiff'];
        }
    }
}

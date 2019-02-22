<?php

/**
 * DeskPRO.
 *
 * @category EmailGateway
 */

namespace Application\DeskPRO\EmailGateway\TicketGateway;

use Application\DeskPRO\App;
use Application\DeskPRO\EmailGateway\InlineImageTokens;
use Application\DeskPRO\EmailGateway\LinkedImages;
use Application\DeskPRO\Entity\EmailAccount;
use Application\DeskPRO\Entity\EmailSource;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Entity\TicketAttachment;
use Application\DeskPRO\Entity\TicketMessage;
use Application\DeskPRO\Monolog\Handler\OrbLoggerAdapterHandler;
use Application\DeskPRO\Translate\Translate;
use DeskPRO\Bundle\AppBundle\Entity\TicketMessageAttribute;
use Orb\Util\Strings;

class ProcessNew extends ProcessAbstract
{
    /**
     * @var TicketIncomingEmail
     */
    protected $ticketEmail;

    /**
     * @var \Orb\Input\Cleaner\Cleaner
     */
    protected $cleaner;

    /**
     * Constructor.
     *
     * @param EmailAccount        $account
     * @param Person              $person
     * @param TicketIncomingEmail $ticket_email
     * @param Translate           $translator
     */
    public function __construct(EmailAccount $account, Person $person, TicketIncomingEmail $ticket_email,
        Translate $translator
    ) {
        $this->account     = $account;
        $this->person      = $person;
        $this->ticketEmail = $ticket_email;
        $this->reader      = $ticket_email->reader;
        $this->cleaner     = App::get('deskpro.core.input_cleaner');
        $this->translator  = $translator;
    }

    /**
     * @throws \Exception
     *
     * @return Ticket|mixed
     */
    public function run()
    {
        $this->person = $this->person;

        //------------------------------
        // Read email body/subject
        //------------------------------

        $inlineImages = new InlineImageTokens($this->reader);

        $emailInfo = new TicketIncomingEmailMessage(
            TicketIncomingEmailMessage::MODE_NEWTICKET,
            null,
            $this->ticketEmail,
            $this->cleaner,
            App::$container->getEmailAccountManager(),
            [$this, 'replaceInlineAttachTokens'],
            [$this, 'processBlobs'],
            $this->getLogger()
        );

        $runReplyCutter = $this->ticketEmail->force_reply_cutter;

        if (!$runReplyCutter) {
            // Auto-detect if we should run the cutter anyway to catch large
            // emails that weren't caught as replies
            if (
                strpos($this->ticketEmail->email_body_html, 'DP_TOP_MARK') !== false
                || strpos($this->ticketEmail->email_body_html, '<!-- DP_MESSAGE_BEGIN -->') !== false
                || substr_count($this->ticketEmail->email_body_html, '>') > 15000
            ) {
                $runReplyCutter = true;
            }
        }

        if ($runReplyCutter) {
            $this->logMessage('[TicketGatewayProcessor] runNewTicket running reply cutter (new ticket from reply)');
        } else {
            if ($this->ticketEmail->email_body_html) {
                $this->logMessage('[TicketGatewayProcessor] runNewTicket read HTML email');
                $emailInfo->body = $this->ticketEmail->email_body_html;

                // Sent from a DeskPRO instance, we should get the specific message by looking for our delims
                // But dont do this cut if its an auto-reply, we want the real message in those cases. The actual notifs we sent
                // are silenced in those cases anyway so the auto-replies are handled like other robot replies
                if (
                    $this->reader->getHeader('X-DeskPRO-Build') && $this->reader->getHeader('X-DeskPRO-Build')->getHeader()
                    && !($this->reader->getHeader('X-DeskPRO-Auto') && $this->reader->getHeader('X-DeskPRO-Auto')->getHeader())
                ) {
                    $body = trim(Strings::extractRegexMatch('#<!\-\- DP_MESSAGE_BEGIN \-\->(.*?)<!\-\- DP_MESSAGE_END \-\->#s', $emailInfo->body, 1));
                    if ($body) {
                        $emailInfo->body = $body;
                    }
                }

                $emailInfo->body_is_html = true;
            } else {
                $this->logMessage('[TicketGatewayProcessor] runNewTicket read text email');
                $bodyText = $this->ticketEmail->email_body_text;
                if (!$bodyText && $this->ticketEmail->email_body_text) {
                    $bodyText                 = $this->ticketEmail->email_body_text;
                    $emailInfo->charset_error = $this->reader->getBodyText()->getOriginalCharset();
                }

                if (strlen($bodyText) > 25000) {
                    $this->logMessage('[TicketGatewayProcessor] Message too long, trimming');
                    $bodyText = substr($bodyText, 0, 25000);
                }

                $emailInfo->body         = Strings::text2html($bodyText, 'plaintext-email');
                $emailInfo->body_is_html = false;
            }

            // Replace inline image tags with tokens
            $emailInfo->body_raw  = $emailInfo->body;
            $emailInfo->body      = $inlineImages->processTokens($emailInfo->body);
            $emailInfo->body_full = '';

            $linkedImages    = new LinkedImages($this->logger);
            $emailInfo->body = $linkedImages->importReplaceLinkedImages($emailInfo->body);

            if ($emailInfo->body_is_html) {
                // The basic cleaner cleans out outlook type stuff like empty <p>'s that cause whitespace
                $emailInfo->body = $this->cleaner->clean($emailInfo->body, 'html_email_preclean');
                $emailInfo->body = $this->cleaner->clean($emailInfo->body, 'html_email_basicclean');
                $emailInfo->body = $this->cleaner->clean($emailInfo->body, 'html_email');
            }

            $emailInfo->body = Strings::trimHtml($emailInfo->body);
        }

        $emailInfo->body = $this->cleaner->clean($emailInfo->body, 'html_email_postclean');
        $emailInfo->body = $this->replaceInlineAttachTokens($emailInfo->body, $inlineImages);

        //------------------------------
        // Try to guess lang based off the email
        //------------------------------

        $useLang = null;

        if (!App::getDataService('Language')->isLangSystemEnabled()) {
            $this->logMessage('[TicketGatewayProcessor] Helpdesk is in single-language mode');
        } elseif ($this->person->getRealLanguage()) {
            $this->logMessage('[TicketGatewayProcessor] Person has language set: '.$this->person->getRealLanguage()->getId().' '.$this->person->getRealLanguage()->getTitle());
        } else {
            if (App::$container->get('settings_resolver')->getGlobalSettings()->get('core.lang_auto_detect')) {
                $detectBody = strip_tags($emailInfo->body);
                if (strlen($detectBody) < 300) {
                    $this->logMessage('[TicketGatewayProcessor] Message too short to attempt lang detection');
                } else {
                    /* @var $langDetect \Application\DeskPRO\Languages\Detect */
                    $langDetect = App::getSystemService('language_detect');
                    $this->logMessage('Detectable languages: '.implode(', ', $langDetect->getDetectableLanguages()));

                    $lang = $langDetect->detectLanguage($detectBody);
                    if ($lang) {
                        $this->logMessage("[TicketGatewayProcessor] Detected language {$lang->getTitle()} (#{$lang->getId()})");
                        $useLang = $lang;
                    }
                }

                if (!$useLang) {
                    $this->logMessage('[TicketGatewayProcessor] No language detected, no language will be set');
                }
            } else {
                $this->logMessage('[TicketGatewayProcessor] Language auto detect is disabled');
            }
        }

        //------------------------------
        // Create user account
        //------------------------------

        if (!$this->person) {
            $this->person = App::getOrm()->getRepository(Person::class)->findOneByEmail($this->reader->getFromAddress()
                ->getEmail());
        }

        // But we'll create them now if they dont
        if (!$this->person) {
            $this->logMessage('[TicketGatewayProcessor] No existing person found, will try and create it');
            $person = Person::newContactPerson([
                'email' => $this->reader->getFromAddress()->getEmail(),
                'name'  => $this->reader->getFromAddress()->getNameUtf8() ?: '',
            ]);

            App::getDb()->beginTransaction();
            try {
                App::getOrm()->persist($person);
                App::getOrm()->flush();
                App::getDb()->commit();
            } catch (\Exception $e) {
                App::getDb()->rollback();
                throw $e;
            }
        }

        //------------------------------
        // Create the ticket
        //------------------------------

        if ($emailInfo->is_no_subject) {
            $subject = App::$container->getTranslator()->phrase('user.tickets.no_subject', [], $useLang);
        } else {
            $subject = $emailInfo->subject;
        }

        $subject = trim($subject);
        if (!$subject) {
            $subject = '(No Subject)';
        }

        $ticket                  = $this->getTicketManager()->createTicket();
        $ticket->subject         = $subject;
        $ticket->person          = $this->person;
        $ticket->status          = 'awaiting_agent';
        $ticket->email_account   = $this->account;
        $ticket->creation_system = 'gateway.person';

        if ($useLang) {
            if ($this->person && !$this->person->getRealLanguage()) {
                $this->person->setLanguage($useLang);
            }

            $ticket->setLanguage($useLang);
        }

        // Set the proper email address on the ticket from the users account
        if (strtolower($this->reader->getFromAddress()->email) != $this->person->getPrimaryEmailAddress()) {
            $emailRec = $this->person->findEmailAddress($this->reader->getFromAddress()->getEmail());
            if ($emailRec) {
                $ticket->person_email = $emailRec;
            }
        }

        $ticketMessage              = new TicketMessage($this->reader->getId());
        $ticketMessage->person      = $this->person;
        $ticketMessage->message_raw = $emailInfo->body_raw;
        $ticketMessage->setMessageHtml($emailInfo->body);
        $ticketMessage->withNewSubject  = $subject;
        $ticketMessage->creation_system = 'gateway.person';
        if ($this->reader->isSigned() !== null) {
            $signed = new TicketMessageAttribute('signed');
            $signed->setValue($this->reader->isSigned());
            $ticketMessage->addAttribute($signed);
        }
        if ($this->reader->getDecryptedMail() !== null) {
            $decrypted = new TicketMessageAttribute('decrypted');
            $decrypted->setValue(true);
            $ticketMessage->addAttribute($decrypted);
        }
        if ($this->reader->getDecryptionError() !== null) {
            $decryptionError = new TicketMessageAttribute('decryption_error');
            $decryptionError->setValue($this->reader->getDecryptionError());
            $ticketMessage->addAttribute($decryptionError);

            $emailSourceBlob = $this->reader->getSourceAsBlob();

            $attach           = new TicketAttachment();
            $attach['blob']   = $emailSourceBlob;
            $attach['person'] = $this->person;
            $ticketMessage->addAttachment($attach);
        }

        if ($this->reader->getProperty('email_source')) {
            $ticketMessage->email_source = $this->reader->getProperty('email_source');
        }

        $ticket->addMessage($ticketMessage);

        foreach ($this->processBlobs() as $blob) {
            $attach           = new TicketAttachment();
            $attach['blob']   = $blob;
            $attach['person'] = $this->person;

            if (isset($this->inline_blobs[$blob->getId()])) {
                $attach->is_inline = true;
            }

            $ticketMessage->addAttachment($attach);

            $blob->is_temp = false;
            App::getOrm()->persist($blob);
        }

        //------------------------------
        // Check for dupe first
        //------------------------------

        $ticketMessage->resetHashCode();

        if ($this->person && !$this->person->isNewPerson()) {
            if ($dupeMessage = App::getOrm()->getRepository(TicketMessage::class)->checkDupeMessage($ticketMessage, null,
                10800, $this->getLogger())) {
                $this->setError(EmailSource::ERR_DUPE);
                $this->logMessage('[TicketGatewayProcessor] Duplicate message '.$dupeMessage->getId());

                return $dupeMessage;
            }
        }

        //------------------------------
        // Reply actions
        //------------------------------

        if ($this->ticketEmail->reply_actions) {
            $replyActionsApply            = new ReplyActionsApplicator($this->ticketEmail->reply_actions, App::getContainer());
            $replyActionsContext          = new ReplyActionsContext();
            $replyActionsContext->ticket  = $ticket;
            $replyActionsContext->message = $ticketMessage;
            $replyActionsApply->apply($replyActionsContext);
        }

        //------------------------------
        // Process new ticket
        //------------------------------

        // User is an agent and the ticket owner isn't the person who submitted
        // the email. Means the agent used the #user action code and is
        // creting a ticket on behalf of someone else
        if ($this->person->isAgent() && $ticket->getPerson() !== $this->person) {
            $executorContext = $this->getTicketManager()->createAgentExecutorContext(
                $this->person,
                'newticket',
                'email'
            );
        } else {
            $executorContext = $this->getTicketManager()->createUserExecutorContext(
                $this->person,
                'newticket',
                'email'
            );
        }

        $executorContext->setEmailContext($this->reader);
        $executorContext->getVars()->set('ticket_email', $this->ticketEmail);

        if ($this->ticketEmail->is_bounce) {
            $executorContext->getVars()->set('is_bounce_message', true);
        }
        if ($this->reader->isFromRobot()) {
            $executorContext->getVars()->set('is_robot_message', true);
        }

        if ($this->logger) {
            $orbLoggerAdapter = new OrbLoggerAdapterHandler($this->logger);
            $executorContext->getLogger()->pushHandler($orbLoggerAdapter);
        }

        App::getDb()->beginTransaction();

        try {
            if ($this->reader->getCcAddresses() || count($this->reader->getToAddresses()) > 1) {
                $this->logMessage('[TicketGatewayProcessor] Has CC');
                $this->handleCc($ticket, $this->reader->getDeliveredAddresses());
            }

            $this->getTicketManager()->saveTicket($ticket, $executorContext);

            if ($emailInfo->charset_error) {
                App::getOrm()->getConnection()->insert('tickets_messages_raw', [
                    'message_id' => $ticketMessage->getId(),
                    'raw'        => $emailInfo->body,
                    'charset'    => $this->charset_error,
                ]);
            }

            $this->logMessage('[TicketGatewayProcessor] Created ticket '.$ticket['id']);

            App::getDb()->commit();
        } catch (\Exception $e) {
            App::getDb()->rollback();
            throw $e;
        }

        return [
            'ticket'         => $ticket,
            'ticket_message' => $ticketMessage,
        ];
    }
}

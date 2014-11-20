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
use Application\DeskPRO\Entity\EmailAccount;
use Application\DeskPRO\Entity\EmailSource;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Entity\TicketAttachment;
use Application\DeskPRO\Entity\TicketMessage;
use Application\DeskPRO\Monolog\Handler\OrbLoggerAdapterHandler;
use Orb\Util\Strings;
use \Application\DeskPRO\Translate\Translate;

class ProcessNew extends ProcessAbstract
{
    /**
     * @var TicketIncomingEmail
     */
    protected $ticket_email;

    /**
     * @var \Application\DeskPRO\Entity\EmailAccount
     */
    protected $account;

    /**
     * @var \Orb\Input\Cleaner\Cleaner
     */
    protected $cleaner;

    /**
     * @param EmailAccount        $account
     * @param Person              $person
     * @param TicketIncomingEmail $ticket_email
     */
    public function __construct(EmailAccount $account, Person $person, TicketIncomingEmail $ticket_email,
        Translate $translator
    ) {
        $this->account       = $account;
        $this->person        = $person;
        $this->ticket_email  = $ticket_email;
        $this->reader        = $ticket_email->reader;
        $this->cleaner       = App::get('deskpro.core.input_cleaner');
        $this->translator    = $translator;
    }

    /**
     * @return Ticket|mixed
     * @throws \Exception
     */
    public function run()
    {
        $this->person = $this->person;

        #------------------------------
        # Read email body/subject
        #------------------------------

        $this->processBlobs();
        $inline_images = new InlineImageTokens($this->reader);

        $email_info = new TicketIncomingEmailMessage(
            TicketIncomingEmailMessage::MODE_NEWTICKET,
            null,
            $this->ticket_email,
            $this->cleaner,
            App::$container->getEmailAccountManager(),
            array($this, 'replaceInlineAttachTokens'),
            $this->getLogger()
        );

        $run_reply_cutter = $this->ticket_email->force_reply_cutter;

        if (!$run_reply_cutter) {
            // Auto-detect if we should run the cutter anyway to catch large
            // emails that weren't caught as replies
            if (
                strpos($this->ticket_email->email_body_html, 'DP_TOP_MARK') !== false
                || strpos($this->ticket_email->email_body_html, '<!-- DP_MESSAGE_BEGIN -->') !== false
                || substr_count($this->ticket_email->email_body_html, '>') > 15000
            ) {
                $run_reply_cutter = true;
            }
        }

        if ($run_reply_cutter) {
            $this->logMessage('[TicketGatewayProcessor] runNewTicket running reply cutter (new ticket from reply)');
        } else {
            if ($this->ticket_email->email_body_html) {
                $this->logMessage('[TicketGatewayProcessor] runNewTicket read HTML email');
                $email_info->body = $this->ticket_email->email_body_html;

                // Sent from a DeskPRO instance, we should get the specific message by looking for our delims
                // But dont do this cut if its an auto-reply, we want the real message in those cases. The actual notifs we sent
                // are silenced in those cases anyway so the auto-replies are handled like other robot replies
                if (
                    $this->reader->getHeader('X-DeskPRO-Build') && $this->reader->getHeader('X-DeskPRO-Build')->getHeader()
                    && !($this->reader->getHeader('X-DeskPRO-Auto') && $this->reader->getHeader('X-DeskPRO-Auto')->getHeader())
                ) {
                    $body = trim(Strings::extractRegexMatch('#<!\-\- DP_MESSAGE_BEGIN \-\->(.*?)<!\-\- DP_MESSAGE_END \-\->#s', $email_info->body, 1));
                    if ($body) {
                        $email_info->body = $body;
                    }
                }

                $email_info->body_is_html = true;
            } else {
                $this->logMessage('[TicketGatewayProcessor] runNewTicket read text email');
                $txt = $this->ticket_email->email_body_text;
                if (!$txt && $this->ticket_email->email_body_text) {
                    $txt                       = $this->ticket_email->email_body_text;
                    $email_info->charset_error = $this->reader->getBodyText()->getOriginalCharset();
                }

                if (strlen($txt) > 25000) {
                    $this->logMessage('[TicketGatewayProcessor] Message too long, trimming');
                    $txt = substr($txt, 0, 25000);
                }

                $email_info->body         = str_replace(array("\n", "\r"), '', nl2br(@htmlspecialchars($txt, \ENT_QUOTES, 'UTF-8')));
                $email_info->body_is_html = false;
            }

            // Replace inline image tags with tokens
            $email_info->body_raw  = $email_info->body;
            $email_info->body      = $inline_images->processTokens($email_info->body);
            $email_info->body_full = '';

            if ($email_info->body_is_html) {
                // The basic cleaner cleans out outlook type stuff like empty <p>'s that cause whitespace
                $email_info->body = $this->cleaner->clean($email_info->body, 'html_email_preclean');
                $email_info->body = $this->cleaner->clean($email_info->body, 'html_email_basicclean');
                $email_info->body = $this->cleaner->clean($email_info->body, 'html_email');
            }

            $email_info->body = Strings::trimHtml($email_info->body);
        }

        $email_info->body = $this->cleaner->clean($email_info->body, 'html_email_postclean');
        $email_info->body = $this->replaceInlineAttachTokens($email_info->body, $inline_images);

        #------------------------------
        # Try to guess lang based off the email
        #------------------------------

        $use_lang = null;

        if (!App::getDataService('Language')->isLangSystemEnabled()) {
            $this->logMessage("Helpdesk is in single-language mode");
        } elseif ($this->person->getRealLanguage()) {
            $this->logMessage("Person has language set: ".$this->person->getRealLanguage()->id." ".$this->person->getRealLanguage()->title);
        } else {
            $detect_body = strip_tags($email_info->body);
            if (strlen($detect_body) < 300) {
                $this->logMessage('Message too short to attempt lang detection');
            } else {
                /** @var $lang_detect \Application\DeskPRO\Languages\Detect */
                $lang_detect = App::getSystemService('language_detect');
                $this->logMessage("Detectable languages: ".implode(', ', $lang_detect->getDetectableLanguages()));

                $lang = $lang_detect->detectLanguage($detect_body);
                if ($lang) {
                    $this->logMessage("Detected language {$lang->title} (#{$lang->id})");
                    $use_lang = $lang;
                }
            }

            if (!$use_lang) {
                $this->logMessage('No language detected, no language will be set');
            }
        }

        #------------------------------
        # Create user account
        #------------------------------

        if (!$this->person) {
            $this->person = App::getOrm()->getRepository('DeskPRO:Person')->findOneByEmail($this->reader->getFromAddress()->getEmail());
        }

        // But we'll create them now if they dont
        if (!$this->person) {
            $this->logMessage('[TicketGatewayProcessor] No existing person found, will try and create it');
            $person = Person::newContactPerson(array(
                'email' => $this->reader->getFromAddress()->getEmail(),
                'name'  => $this->reader->getFromAddress()->getNameUtf8() ?: '',
            ));

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

        $executor_context = $this->getTicketManager()->createUserExecutorContext(
            $this->person,
            'newticket',
            'email'
        );

        $executor_context->setEmailContext($this->reader);
        $executor_context->getVars()->set('ticket_email', $this->ticket_email);

        if ($this->logger) {
            $orb_logger_adapter = new OrbLoggerAdapterHandler($this->logger);
            $executor_context->getLogger()->pushHandler($orb_logger_adapter);
        }

        #------------------------------
        # Create the ticket
        #------------------------------

        if ($email_info->is_no_subject) {
            $subject = App::$container->getTranslator()->phrase('user.tickets.no_subject', array(), $use_lang);
        } else {
            $subject = $email_info->subject;
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

        if ($use_lang) {
            $ticket->language = $use_lang;
        }

        // Set the proper email address on the ticket from the users account
        if (strtolower($this->reader->getFromAddress()->email) != $this->person->getPrimaryEmailAddress()) {
            $email_rec = $this->person->findEmailAddress($this->reader->getFromAddress()->getEmail());
            if ($email_rec) {
                $ticket->person_email = $email_rec;
            }
        }

        $ticket_message              = new TicketMessage();
        $ticket_message->person      = $this->person;
        $ticket_message->message_raw = $email_info->body_raw;
        $ticket_message->setMessageHtml($email_info->body);
        $ticket_message->withNewSubject  = $subject;
        $ticket_message->creation_system = 'gateway.person';

        if ($this->reader->getProperty('email_source')) {
            $ticket_message->email_source = $this->reader->getProperty('email_source');
        }

        $ticket->addMessage($ticket_message);

        foreach ($this->processBlobs() as $blob) {
            $attach           = new TicketAttachment();
            $attach['blob']   = $blob;
            $attach['person'] = $this->person;

            if (isset($this->inline_blobs[$blob->id])) {
                $attach->is_inline = true;
            }

            $ticket_message->addAttachment($attach);

            $blob->is_temp = false;
            App::getOrm()->persist($blob);
        }

        #------------------------------
        # Check for dupe first
        #------------------------------

        if ($this->person && !$this->person->isNewPerson()) {
            if ($dupe_message = App::getOrm()->getRepository('DeskPRO:TicketMessage')->checkDupeMessage($ticket_message, null, 10800, $this->getLogger())) {
                $this->setError(EmailSource::ERR_DUPE);
                $this->logMessage('[TicketGatewayProcessor] Duplicate message '.$dupe_message->getId());

                return $dupe_message;
            }
        }

        #------------------------------
        # Reply actions
        #------------------------------

        if ($this->ticket_email->reply_actions) {
            $reply_actions_apply            = new ReplyActionsApplicator($this->ticket_email->reply_actions, App::getContainer());
            $reply_actions_context          = new ReplyActionsContext();
            $reply_actions_context->ticket  = $ticket;
            $reply_actions_context->message = $ticket_message;
            $reply_actions_apply->apply($reply_actions_context);
        }

        #------------------------------
        # Process new ticket
        #------------------------------

        App::getDb()->beginTransaction();

        try {
            if ($this->reader->getCcAddresses() || count($this->reader->getToAddresses()) > 1) {
                $this->logMessage('[TicketGatewayProcessor] Has CC');
                $this->handleCc($ticket, $this->reader->getDeliveredAddresses());
            }

            $this->getTicketManager()->saveTicket($ticket, $executor_context);

            if ($email_info->charset_error) {
                App::getOrm()->getConnection()->insert('tickets_messages_raw', array(
                    'message_id' => $ticket_message->id,
                    'raw'        => $email_info->body,
                    'charset'    => $this->charset_error,
                ));
            }

            $this->logMessage('[TicketGatewayProcessor] Created ticket '.$ticket['id']);

            App::getDb()->commit();
        } catch (\Exception $e) {
            App::getDb()->rollback();
            throw $e;
        }

        return $ticket;
    }
}

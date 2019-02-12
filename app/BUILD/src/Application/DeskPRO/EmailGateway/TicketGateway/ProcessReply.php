<?php

/**
 * DeskPRO.
 *
 * @category EmailGateway
 */

namespace Application\DeskPRO\EmailGateway\TicketGateway;

use Application\DeskPRO\App;
use Application\DeskPRO\EmailGateway\LinkedImages;
use Application\DeskPRO\Entity\EmailAccount;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Entity\TicketAttachment;
use Application\DeskPRO\Entity\TicketMessage;
use Application\DeskPRO\Monolog\Handler\OrbLoggerAdapterHandler;
use Application\DeskPRO\Translate\Translate;
use DeskPRO\Bundle\AppBundle\Entity\TicketMessageAttribute;
use Orb\Types\NoValue;

class ProcessReply extends ProcessAbstract
{
    /**
     * @var TicketIncomingEmail
     */
    protected $ticket_email;

    /**
     * @var \Application\DeskPRO\Entity\Ticket
     */
    protected $ticket;

    /**
     * @var \Orb\Input\Cleaner\Cleaner
     */
    protected $cleaner;

    /**
     * @param EmailAccount        $account
     * @param Ticket              $ticket
     * @param Person              $person
     * @param TicketIncomingEmail $ticket_email
     */
    public function __construct(EmailAccount $account, Ticket $ticket, Person $person, TicketIncomingEmail $ticket_email, Translate $translator)
    {
        $this->account      = $account;
        $this->ticket       = $ticket;
        $this->person       = $person;
        $this->ticket_email = $ticket_email;
        $this->reader       = $ticket_email->reader;
        $this->cleaner      = App::get('deskpro.core.input_cleaner');
        $this->translator   = $translator;
    }

    /**
     * @param string $context
     *
     * @throws \Exception
     *
     * @return \Application\DeskPRO\Mail\Message|mixed|null|TicketMessage
     */
    public function run($context = 'user')
    {
        $this->logMessage("doNewReply context $context");

        if ($context == 'user') {
            $executor_context = $this->getTicketManager()->createUserExecutorContext(
                $this->person,
                'newreply',
                'email'
            );
        } else {
            $executor_context = $this->getTicketManager()->createAgentExecutorContext(
                $this->person,
                'newreply',
                'email'
            );

            $this->person->loadHelper('PermissionsManager');
            if (!$this->person->PermissionsManager->TicketChecker->canReply($this->ticket)) {
                if (!$this->ticket_email->is_bounce && !$this->reader->isFromRobot()) {
                    $message = App::getMailer()->createMessage();
                    $message->setTemplate('DeskPRO:emails_agent:error-no-reply-perm.html.twig', [
                        'ticket'  => $this->ticket,
                        'subject' => $this->reader->getSubject()->getSubjectUtf8(),
                        'name'    => $this->reader->getFromAddress()->getName() ?: $this->reader->getFromAddress()->getEmail(),
                    ]);
                    $message->setTo($this->reader->getFromAddress()->getEmail());

                    App::$container->get('mailer.utils')->sendWithPersonContext($message, $this->person);
                }

                $this->setError('perm_insufficient');

                return;
            }
        }

        $executor_context->setEmailContext($this->reader);
        $executor_context->getVars()->set('ticket_email', $this->ticket_email);

        if ($this->logger) {
            $orb_logger_adapter = new OrbLoggerAdapterHandler($this->logger);
            $executor_context->getLogger()->pushHandler($orb_logger_adapter);
        }

        if ($this->ticket_email->is_dp3_reply) {
            $this->logMessage('doNewReply message class: TicketIncomingEmailMessageV3');
            $email_info = new TicketIncomingEmailMessageV3(
                $this->ticket,
                $this->ticket_email,
                $this->cleaner,
                [$this, 'replaceInlineAttachTokens'],
                [$this, 'processBlobs'],
                $this->getLogger()
            );
        } else {
            $this->logMessage('doNewReply message class: TicketIncomingEmailMessage');
            $email_info = new TicketIncomingEmailMessage(
                TicketIncomingEmailMessage::MODE_NEWREPLY,
                $this->ticket,
                $this->ticket_email,
                $this->cleaner,
                App::$container->getEmailAccountManager(),
                [$this, 'replaceInlineAttachTokens'],
                [$this, 'processBlobs'],
                $this->getLogger()
            );
        }

        if (App::getSetting('core_tickets.gateway_agent_require_marker') && $context == 'agent' && !$email_info->found_top_marker) {
            // The marker is required for agent emails
            $this->logMessage('doNewRelpy agent reply missing marker');
            $this->setError('missing_marker');

            if (App::getContainer()->get('deskpro.feature_flags')->hasBeta('email_templates')) {
                $viewModel = App::getContainer()->get('email.agent_viewmodel_factory')
                    ->createAgentErrorMarkerMissingModel($this->ticket, $this->reader->getSubject()->getSubjectUtf8());
                App::getContainer()->get('mailer.utils')->sendModelWithPersonContext(
                    $this->person,
                    $viewModel,
                    ['to' => $this->reader->getFromAddress()->getEmail()]
                );
            } else {
                $message = App::getMailer()->createMessage();
                $message->setTemplate('DeskPRO:emails_agent:error-marker-missing.html.twig', [
                    'ticket'  => $this->ticket,
                    'subject' => $this->reader->getSubject()->getSubjectUtf8(),
                    'name'    => $this->reader->getFromAddress()->getName() ?: $this->reader->getFromAddress()->getEmail(),
                ]);
                $message->setTo($this->reader->getFromAddress()->getEmail());

                App::$container->get('mailer.utils')->sendWithPersonContext($message, $this->person);
            }

            return;
        }

        $linkedImages     = new LinkedImages($this->logger);
        $email_info->body = $linkedImages->importReplaceLinkedImages($email_info->body);

        $message               = new TicketMessage($this->reader->getId());
        $message->email_reader = $this->reader;
        if ($this->reader->isSigned() !== null) {
            $signed = new TicketMessageAttribute('signed');
            $signed->setValue($this->reader->isSigned());
            $message->addAttribute($signed);
        }
        if ($this->reader->getDecryptedMail() !== null) {
            $decrypted = new TicketMessageAttribute('decrypted');
            $decrypted->setValue(true);
            $message->addAttribute($decrypted);
        }
        if ($this->reader->getDecryptionError() !== null) {
            $decryptionError = new TicketMessageAttribute('decryption_error');
            $decryptionError->setValue($this->reader->getDecryptionError());
            $message->addAttribute($decryptionError);
        }
        if ($this->reader->hasProperty('email_source')) {
            $message['email_source'] = $this->reader->getProperty('email_source');
        }

        if ($this->ticket_email->is_bounce) {
            $executor_context->getVars()->set('is_bounce_message', true);
            $message->is_agent_note = true;
        }
        if ($this->reader->isFromRobot()) {
            $executor_context->getVars()->set('is_robot_message', true);
        }

        if ($this->person->is_agent) {
            $message->creation_system = 'gateway.agent';
        } else {
            $message->creation_system = 'gateway.person';
        }

        $message['ticket'] = $this->ticket;
        $message['person'] = $this->person;
        $message['email']  = $this->reader->getFromAddress()->getEmail();

        $message['message']      = $email_info->body;
        $message['message_full'] = $email_info->body_full;
        $message['message_raw']  = $email_info->body_raw;

        $message['show_full_hint'] = false;
        $inline_reply_detector     = new DetectInlineReply(App::getOrm(), $this->reader);
        if ($this->getLogger()) {
            $inline_reply_detector->setLogger($this->getLogger());
        }

        if ($inline_reply_detector->hasDifferentMessage() && $message['message_full']) {
            $message['show_full_hint'] = true;
        }

        if ($this->person->is_agent && $context === 'agent') {
            $default_as_note = App::getSetting('core_tickets.email_reply_as_note');

            if (!$email_info->agent_reply_mode_foundflag) {
                if ($default_as_note) {
                    $this->logMessage('[TicketGatewayProcessor] No reply mode flag found, defaulting to setting: reply as note');
                    $email_info->agent_reply_as_note = true;
                } else {
                    $this->logMessage('[TicketGatewayProcessor] No reply mode flag found, defaulting to setting: reply as reply');
                    $email_info->agent_reply_as_note = false;
                }
            }

            if (!$email_info->agent_reply_as_note || isset($this->ticket_email->reply_actions['is_reply'])) {
                $this->logMessage('Reply mode: reply');
                $message['is_agent_note']          = false;
                $this->ticket->email_reader_action = 'agent_reply';
            } else {
                $this->logMessage('Reply mode: note');
                $message['is_agent_note']          = true;
                $this->ticket->email_reader_action = 'agent_note';
            }
        }

        $ticket_attach = [];
        foreach ($this->processBlobs() as $blob) {
            if (isset($this->dupe_inline_blobs[$blob->getId()])) {
                continue;
            }

            $attach           = new TicketAttachment();
            $attach['blob']   = $blob;
            $attach['person'] = $this->person;

            if (isset($this->inline_blobs[$blob->getId()])) {
                $attach->is_inline = true;
            }

            $message->addAttachment($attach);
            $ticket_attach[] = $attach;
        }

        $has_message = true;
        if (!$ticket_attach && !trim(strip_tags($email_info->body))) {
            $has_message = false;
        }

        $has_reply_codes = false;
        if ($this->ticket_email->reply_actions) {
            $has_reply_codes = true;
        }

        $message->resetHashCode();

        // - Only add the message if we have an actual message
        // This allows email replies with action codes but no reply,
        // so the "empty reply" isnt processed as a reply
        $did_add_message = false;
        if (!isset($this->ticket_email->reply_actions['no_reply']) && ($has_message || ($has_reply_codes && !$has_message))) {
            $this->logMessage('[TicketGatewayProcessor] Checking for dupe message: '.$message->getMessageHash());

            $did_add_message = true;
            if ($dupe_message = App::getOrm()->getRepository('DeskPRO:TicketMessage')->checkDupeMessage($message, $this->ticket, 10800, $this->getLogger())) {
                $this->setError('duplicate_message');
                $this->logMessage('[TicketGatewayProcessor] doNewReply duplicate message '.$dupe_message->getId());

                // Reset some objects so they dont get flushed during next loop
                App::getOrm()->detach($this->ticket);
                App::getOrm()->detach($message);

                foreach ($ticket_attach as $a) {
                    $a->ticket  = null;
                    $a->message = null;
                    App::getOrm()->detach($a);
                }

                return $dupe_message;
            }

            $this->ticket->addMessage($message);
        } else {
            if (isset($this->ticket_email->reply_actions['no_reply'])) {
                $this->logMessage('No reply because of #noreply tag');
            } else {
                $this->logMessage('No reply because empty reply');
            }

            if ($context == 'user') {
                $this->setError('empty');

                // Reset some objects so they dont get flushed during next loop
                App::getOrm()->detach($this->ticket);
                App::getOrm()->detach($message);

                foreach ($ticket_attach as $a) {
                    $a->ticket  = null;
                    $a->message = null;
                    App::getOrm()->detach($a);
                }

                return;
            }
        }

        if ($this->reader->getCcAddresses() || count($this->reader->getToAddresses()) > 1) {
            $this->logMessage('[TicketGatewayProcessor] Has CC');
            $this->handleCc($this->ticket, $this->reader->getDeliveredAddresses());
        }

        //------------------------------
        // Reply actions
        //------------------------------

        if ($this->ticket_email->reply_actions) {
            $reply_actions_apply           = new ReplyActionsApplicator($this->ticket_email->reply_actions, App::getContainer());
            $reply_actions_context         = new ReplyActionsContext();
            $reply_actions_context->ticket = $this->ticket;
            if ($did_add_message) {
                $reply_actions_context->message = $message;
            }
            $reply_actions_apply->apply($reply_actions_context);
        }

        //------------------------------
        // Default switch status
        //------------------------------

        if (!$this->ticket_email->is_bounce && !$message->is_agent_note && $did_add_message && !isset($this->ticket_email->reply_actions['status'])) {
            if ($this->person['is_agent'] && $context == 'agent') {
                $this->logMessage('[TicketGatewayProcessor] doNewReply set status = awaiting_user');
                $this->ticket['status'] = Ticket::STATUS_AWAITING_USER;
            } else {
                $this->logMessage('[TicketGatewayProcessor] doNewReply set status = awaiting_agent');
                $this->ticket['status'] = Ticket::STATUS_AWAITING_AGENT;
            }
        }

        App::getDb()->beginTransaction();

        try {
            App::getOrm()->persist($this->person);
            App::getOrm()->persist($this->ticket);
            if ($did_add_message) {
                App::getOrm()->persist($message);
            }

            $this->getTicketManager()->saveTicket($this->ticket, $executor_context);
            App::getOrm()->flush();

            if ($email_info->charset_error) {
                App::getOrm()->getConnection()->insert('tickets_messages_raw', [
                    'message_id' => $message['id'],
                    'raw'        => $email_info->body,
                    'charset'    => $email_info->charset_error,
                ]);
            }
            App::getDb()->commit();
        } catch (\Exception $e) {
            App::getDb()->rollback();
            throw $e;
        }

        if ($message) {
            return $message;
        } else {
            return NoValue::get();
        }
    }
}

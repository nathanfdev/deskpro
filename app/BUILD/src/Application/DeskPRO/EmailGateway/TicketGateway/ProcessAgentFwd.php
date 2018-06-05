<?php

/**
 * DeskPRO.
 *
 * @category EmailGateway
 */

namespace Application\DeskPRO\EmailGateway\TicketGateway;

use Application\DeskPRO\App;
use Application\DeskPRO\EmailGateway\Cutter\ForwardCutter;
use Application\DeskPRO\EmailGateway\PersonFromEmailProcessor;
use Application\DeskPRO\EmailGateway\Reader\Item\Attachment;
use Application\DeskPRO\EmailGateway\Reader\Item\EmailAddress;
use Application\DeskPRO\Entity\EmailAccount;
use Application\DeskPRO\Entity\EmailSource;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Entity\TicketAttachment;
use Application\DeskPRO\Entity\TicketMessage;
use DeskPRO\Bundle\AppBundle\Entity\TicketMessageAttribute;
use Orb\Util\Strings;

class ProcessAgentFwd extends ProcessAbstract
{
    /**
     * @var \Application\DeskPRO\Entity\Person
     */
    protected $person;

    /**
     * @var TicketIncomingEmail
     */
    protected $ticket_email;

    /**
     * @var \Orb\Input\Cleaner\Cleaner
     */
    protected $cleaner;

    /**
     * @param EmailAccount        $account
     * @param Person              $person
     * @param TicketIncomingEmail $ticket_email
     */
    public function __construct(EmailAccount $account, Person $person, TicketIncomingEmail $ticket_email)
    {
        $this->account      = $account;
        $this->person       = $person;
        $this->ticket_email = $ticket_email;
        $this->reader       = $ticket_email->reader;
        $this->cleaner      = App::get('deskpro.core.input_cleaner');
    }

    /**
     * {@inheritdoc}
     */
    public function run()
    {
        $this->logMessage('[TicketGatewayProcessor] Forwarded ticket by '.$this->person->getId().' '.$this->person->getDisplayContact());

        $executorContext = $this->getTicketManager()->createAgentExecutorContext(
            $this->person,
            'newticket',
            'email'
        );

        $executorContext->setEmailContext($this->reader);
        $executorContext->getVars()->set('ticket_email', $this->ticket_email);

        //------------------------------
        // Read in email props and create cutter
        //------------------------------

        $emailInfo            = [];
        $emailInfo['subject'] = $this->reader->getSubject()->subject;
        if ($emailInfo['body'] = $this->ticket_email->email_body_text) {
            $emailInfo['body_is_html'] = false;
            $fromHtml                  = false;
        } else {
            $emailInfo['body']         = $this->ticket_email->email_body_html;
            $emailInfo['body_is_html'] = false;
            $emailInfo['body']         = Strings::html2Text($emailInfo['body']);
            $fromHtml                  = true;
        }

        $cutter    = new \Application\DeskPRO\EmailGateway\Cutter\Def\Generic();
        $fwdCutter = new ForwardCutter($emailInfo['body'], $emailInfo['body_is_html'], $cutter);

        // Cutter failed on text part,
        // try again on HTML we manually convert to text (sometimes works!)
        if (!$fwdCutter->isValid() && !$fromHtml) {
            $emailInfo['body'] = \Orb\Util\Strings::html2Text($this->ticket_email->email_body_html);
            $fwdCutter         = new \Application\DeskPRO\EmailGateway\Cutter\ForwardCutter($emailInfo['body'], $emailInfo['body_is_html'], $cutter);
        }

        if (!$fwdCutter->isValid()) {
            $this->logMessage('[TicketGatewayProcessor] Invalid forward');

            $hasEmlAttach = false;
            foreach ($this->reader->getAttachments() as $attach) {
                if ($attach->mime_type == 'message/rfc822') {
                    $hasEmlAttach = $attach;
                    break;
                }
            }

            if ($hasEmlAttach) {
                return $this->runNewForwardedEmailAsAttachTicket($hasEmlAttach);
            }

            if ($fwdCutter->getErrorCode() == 'unknown_email') {
                $this->setError(EmailSource::ERR_INVALID_FWD_EMAIL);
            } else {
                $this->setError(EmailSource::ERR_INVALID_FWD);
            }

            if (App::getContainer()->get('deskpro.feature_flags')->hasBeta('email_templates')) {
                $viewModel = App::getContainer()->get('email.agent_viewmodel_factory')
                    ->createAgentErrorInvalidForwardModel($this->error);
                App::$container->get('mailer.utils')->sendModelWithPersonContext(
                    $this->person,
                    $viewModel,
                    [
                        'to'          => $this->reader->getFromAddress()->getEmail(),
                        'attachments' => [\Swift_Attachment::newInstance(
                            $this->reader->getRawSource(),
                            'message.eml',
                            'message/rfc822'
                        )],
                    ]
                );
            } else {
                $message = App::getMailer()->createMessage();
                $message->setSuppressAutoreplies(true);
                $message->setTemplate('DeskPRO:emails_agent:error-invalid-forward.html.twig', [
                    'subject' => $this->reader->getSubject()->getSubjectUtf8(),
                    'name'    => $this->reader->getFromAddress()->getName() ?: $this->reader->getFromAddress()->getEmail(),
                    'error'   => $this->error,
                ]);
                $message->setTo($this->reader->getFromAddress()->getEmail());
                $message->attach(\Swift_Attachment::newInstance(
                    $this->reader->getRawSource(),
                    'message.eml',
                    'message/rfc822'
                ));

                App::$container->get('mailer.utils')->sendWithPersonContext($message, $this->person);
            }

            return;
        }

        $emailInfo['subject'] = ForwardCutter::cutSubjectForwardPrefix($emailInfo['subject']);

        $agentReply = $fwdCutter->getReply();

        if ($agentReply) {
            $agentReply = $this->cleanBodyText($agentReply);
        }

        //------------------------------
        // Find person
        //------------------------------

        $personProcessor = new PersonFromEmailProcessor();
        $personEmailItem = $fwdCutter->getUserEmailItem();

        $user = $personProcessor->findPerson($personEmailItem);
        if ($user) {
            $personProcessor->passPerson($personEmailItem, $user);
        } else {
            $user = $personProcessor->createPerson($personEmailItem);
        }
        if (!$personProcessor->isPersonAssociatedWithAccountBrands($this->account, $user)) {
            $brand = $personProcessor->associatePersonWithAccountBrand($this->account, $user, $forceRegEnabled = false);
            if ($brand) {
                $this->logMessage("[TicketGatewayProcessor] Add Person #{$user->id} to Account Brand #{$brand->id}");
            }
        }

        //------------------------------
        // Create the ticket
        //------------------------------

        $subject = $emailInfo['subject'];
        if (!$subject) {
            $subject = App::$container->getTranslator()->phrase('user.tickets.no_subject');
        }

        $ticket                  = $this->getTicketManager()->createTicket();
        $ticket->subject         = $subject;
        $ticket->person          = $user;
        $ticket->status          = 'awaiting_agent';
        $ticket->email_account   = $this->account;
        $ticket->creation_system = 'gateway.agent';

        $ticketMessage                  = new TicketMessage($this->reader->getId());
        $ticketMessage->person          = $user;
        $ticketMessage->creation_system = 'gateway.agent';
        $ticketMessage->withNewSubject  = $ticket->subject;

        $body = $fwdCutter->getForwardedMessage();
        $body = $this->cleanBodyText($body);
        $ticketMessage->setMessageText($body);
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
        }

        if ($this->reader->getProperty('email_source')) {
            $ticketMessage->email_source = $this->reader->getProperty('email_source');
        }

        $ticket->addMessage($ticketMessage);

        // Add agent reply if there was one
        $agentTicketMessage = null;
        if ($agentReply) {
            $this->logMessage('[TicketGatewayProcessor] Adding agent reply');
            $agentReply = Strings::text2html($agentReply, 'plaintext-email');

            $agentTicketMessage = new TicketMessage($this->reader->getId());
            $agentTicketMessage->date_created->modify('+1 second');
            $agentTicketMessage->person = $this->person;
            $agentTicketMessage->setMessageHtml($agentReply);
            $agentTicketMessage->creation_system = 'gateway.agent';

            if (isset($this->ticket_email->reply_actions['is_reply'])) {
                $this->logMessage('is_reply flag is set');
                $agentTicketMessage->is_agent_note = false;
            } elseif (isset($this->ticket_email->reply_actions['is_note'])) {
                $this->logMessage('is_note flag is set');
                $agentTicketMessage->is_agent_note = true;
            } elseif (App::getSetting('core_tickets.email_fwd_reply_as_note')) {
                $this->logMessage('email_fwd_reply_as_note is enabled');
                $agentTicketMessage->is_agent_note = true;
            } else {
                $this->logMessage('email_fwd_reply_as_note is NOT enabled');
                $agentTicketMessage->is_agent_note = false;
            }

            $ticket->addMessage($agentTicketMessage);
            if ($agentTicketMessage->is_agent_note) {
                $ticket->setStatus('awaiting_agent');
            } else {
                $ticket->setStatus('awaiting_user');
            }
        }

        foreach ($this->processBlobs() as $blob) {
            $attach           = new TicketAttachment();
            $attach['blob']   = $blob;
            $attach['person'] = $this->person;

            if (isset($this->inline_blobs[$blob->id])) {
                $attach->is_inline = true;
            }

            if ($agentTicketMessage) {
                $agentTicketMessage->addAttachment($attach);
            } else {
                $ticketMessage->addAttachment($attach);
            }

            $blob->is_temp = false;
            App::getOrm()->persist($blob);
        }

        $this->logMessage('[TicketGatewayProcessor] (ProcessAgentFwd) run :: Checking for dupe message: '.$ticketMessage->getMessageHash());
        if ($dupe_message = App::getOrm()->getRepository(TicketMessage::class)->checkDupeMessage($ticketMessage, null, 10800, $this->getLogger())) {
            $this->setError('duplicate_message');
            $this->logMessage('[TicketGatewayProcessor] (ProcessAgentFwd) run :: duplicate message '.$dupe_message->getId());

            $message = App::getMailer()->createMessage();
            $message->setSuppressAutoreplies(true);
            $message->setTemplate('DeskPRO:emails_agent:error-dupe-forward.html.twig', [
                'subject'       => $this->reader->getSubject()->getSubjectUtf8(),
                'name'          => $this->reader->getFromAddress()->getName() ?: $this->reader->getFromAddress()->getEmail(),
                'error'         => $this->error,
                'old_ticket_id' => $dupe_message->ticket->id,
            ]);
            $message->setTo($this->reader->getFromAddress()->getEmail());
            $message->attach(\Swift_Attachment::newInstance(
                $this->reader->getRawSource(),
                'message.eml',
                'message/rfc822'
            ));

            App::$container->get('mailer.utils')->sendWithPersonContext($message, $this->person);

            return;
        }

        $trackerExtras = [
            'fwd_via_agent' => $this->person,
        ];
        if ($this->person->getPref('agent_notify_override.forward.email')) {
            $trackerExtras['force_notify_email'] = [$this->person->id];
        }
        if ($this->person->getPref('agent_notify_override.forward.alert')) {
            $trackerExtras['force_notify_alert'] = [$this->person->id];
        }
        $fwdInfo = $fwdCutter->getData();
        if (!empty($fwdInfo['fwd_cc_unknown'])) {
            $trackerExtras['fwd_cc_unknown'] = $fwdInfo['fwd_cc_unknown'];
        }

        // Handle CC's
        $fwdData  = $fwdCutter->getData();
        $ccEmails = $this->reader->getDeliveredAddresses();

        if ($fwdData['fwd_cc_addresses']) {
            foreach ($fwdData['fwd_cc_addresses'] as $e) {
                $emailAddress        = new EmailAddress();
                $emailAddress->email = $e['email'];
                $emailAddress->name  = $e['name'];

                $ccEmails[] = $emailAddress;
            }
        }

        //------------------------------
        // Reply actions
        //------------------------------

        if ($this->ticket_email->reply_actions) {
            $replyActionsApply           = new ReplyActionsApplicator($this->ticket_email->reply_actions, App::getContainer());
            $replyActionsContext         = new ReplyActionsContext();
            $replyActionsContext->ticket = $ticket;
            if ($agentTicketMessage) {
                $replyActionsContext->message = $agentTicketMessage;
            } else {
                $replyActionsContext->message = $ticketMessage;
            }
            $replyActionsApply->apply($replyActionsContext);
        }

        //------------------------------
        // Process new ticket
        //------------------------------

        App::getDb()->beginTransaction();

        try {
            if ($ccEmails) {
                $this->handleCc($ticket, $ccEmails);
            }

            $this->getTicketManager()->saveTicket($ticket, $executorContext);
            $this->logMessage('[TicketGatewayProcessor] Created ticket '.$ticket['id']);

            App::getDb()->commit();
        } catch (\Exception $e) {
            App::getDb()->rollback();
            throw $e;
        }

        return [
            'ticket'               => $ticket,
            'ticket_message'       => $ticketMessage,
            'agent_ticket_message' => $agentTicketMessage,
        ];
    }

    /**
     * @param Attachment $has_eml_attach
     *
     * @throws \Exception
     *
     * @return Ticket
     */
    private function runNewForwardedEmailAsAttachTicket(Attachment $has_eml_attach)
    {
        $executorContext = $this->getTicketManager()->createAgentExecutorContext(
            $this->person,
            'newticket',
            'email'
        );

        $executorContext->setEmailContext($this->reader);
        $executorContext->getVars()->set('ticket_email', $this->ticket_email);

        $userRawSource = $has_eml_attach->getFileContents();
        $userReader    = App::$container->getEmailEzcReaderFactory()->create();
        $userReader->setRawSource($userRawSource);
        $userReader->setProperty('email_source', $userRawSource);

        $this->logMessage('[TicketGatewayProcessor] Forwarded attached ticket by '.$this->person->getId().' '.$this->person->getDisplayContact());

        if ($this->reader->getBodyHtml() && $this->reader->getBodyHtml()->body_utf8) {
            $this->logMessage('[TicketGatewayProcessor] (Agent) Reading html');
            $agentReply = $this->reader->getBodyHtml()->body_utf8;

            $agentReply = $this->importReplaceLinkedImages($agentReply);

            $agentReply = $this->cleaner->clean($agentReply, 'html_email_preclean');
            $agentReply = $this->cleaner->clean($agentReply, 'html_email_basicclean');
            $agentReply = $this->cleaner->clean($agentReply, 'html_email');
            $agentReply = Strings::trimHtmlAdvanced($agentReply);
            $agentReply = $this->cleaner->clean($agentReply, 'html_email_postclean');
        } else {
            $this->logMessage('[TicketGatewayProcessor] (Agent) Reading text');
            $agentReply = trim($this->reader->getBodyText()->body_utf8);
            if ($agentReply) {
                $agentReply = Strings::text2html($agentReply, 'plaintext-email');
            }
        }

        if ($agentReply && !trim(str_replace('&nbsp;', '', strip_tags($agentReply)))) {
            $agentReply = null;
        }

        //------------------------------
        // Verify forward
        //------------------------------

        $personEmailItem = $userReader->getFromAddress();

        $badEmail = false;
        $badBody  = false;

        if (!$personEmailItem || !$personEmailItem->getEmail()) {
            $badEmail = true;
        }

        if (!$userReader->getBodyHtml()->getBodyUtf8() && !$userReader->getBodyText()->getBodyUtf8()) {
            $badBody = true;
        }

        if ($badEmail || $badBody) {
            if ($badEmail) {
                $this->setError(EmailSource::ERR_INVALID_FWD_EMAIL);
            } else {
                $this->setError(EmailSource::ERR_INVALID_FWD);
            }

            if (App::getContainer()->get('deskpro.feature_flags')->hasBeta('email_templates')) {
                $viewModel = App::getContainer()->get('email.agent_viewmodel_factory')
                    ->createAgentErrorInvalidForwardModel($this->error);
                App::getContainer()->get('mailer.utils')->sendModelWithPersonContext(
                    $this->person,
                    $viewModel,
                    [
                        'to'          => $this->reader->getFromAddress()->getEmail(),
                        'attachments' => [\Swift_Attachment::newInstance(
                            $this->reader->getRawSource(),
                            'message.eml',
                            'message/rfc822'
                        )],
                    ]
                );
            } else {
                $message = App::getMailer()->createMessage();
                $message->setSuppressAutoreplies(true);
                $message->setTemplate('DeskPRO:emails_agent:error-invalid-forward.html.twig', [
                    'subject' => $this->reader->getSubject()->getSubjectUtf8(),
                    'name'    => $this->reader->getFromAddress()->getName() ?: $this->reader->getFromAddress()->getEmail(),
                    'error'   => $this->error,
                ]);
                $message->setTo($this->reader->getFromAddress()->getEmail());
                $message->attach(\Swift_Attachment::newInstance(
                    $this->reader->getRawSource(),
                    'message.eml',
                    'message/rfc822'
                ));

                App::$container->get('mailer.utils')->sendWithPersonContext($message, $this->person);
            }

            return;
        }

        //------------------------------
        // Find person
        //------------------------------

        $personProcessor = new PersonFromEmailProcessor();

        $user = $personProcessor->findPerson($personEmailItem);
        if ($user) {
            $personProcessor->passPerson($personEmailItem, $user);
        } else {
            $user = $personProcessor->createPerson($personEmailItem);
        }
        if (!$personProcessor->isPersonAssociatedWithAccountBrands($this->account, $user)) {
            $brand = $personProcessor->associatePersonWithAccountBrand($this->account, $user, $forceRegEnabled = false);
            if ($brand) {
                $this->logMessage("[TicketGatewayProcessor] Add Person #{$user->id} to Account Brand #{$brand->id}");
            }
        }

        //------------------------------
        // Create the ticket
        //------------------------------

        $subject = $userReader->getSubject()->getSubjectUtf8();
        if (!$subject) {
            $subject = App::$container->getTranslator()->phrase('user.tickets.no_subject');
        }

        $ticket                  = $this->getTicketManager()->createTicket();
        $ticket->subject         = $subject;
        $ticket->person          = $user;
        $ticket->status          = 'awaiting_agent';
        $ticket->email_account   = $this->account;
        $ticket->creation_system = 'gateway.agent';

        $ccAddresses = $userReader->getCcAddresses();
        if ($ccAddresses) {
            $this->handleCc($ticket, $ccAddresses);
        }

        $ticketMessage                  = new TicketMessage($userReader->getId());
        $ticketMessage->person          = $user;
        $ticketMessage->creation_system = 'gateway.agent';
        $ticketMessage->withNewSubject  = $ticket->subject;

        if ($userReader->getBodyHtml() && $userReader->getBodyHtml()->body_utf8) {
            $this->logMessage('[TicketGatewayProcessor] (User) Reading html');
            $body = $userReader->getBodyHtml()->body_utf8;

            $body = $this->importReplaceLinkedImages($body);

            $body = $this->cleaner->clean($body, 'html_email_preclean');
            $body = $this->cleaner->clean($body, 'html_email_basicclean');
            $body = $this->cleaner->clean($body, 'html_email');
            $body = Strings::trimHtmlAdvanced($body);
            $body = $this->cleaner->clean($body, 'html_email_postclean');
        } else {
            $this->logMessage('[TicketGatewayProcessor] (User) Reading text');
            $body = Strings::text2html($userReader->getBodyText()->body_utf8, 'plaintext-email');
        }
        $ticketMessage->setMessageHtml($body);
        if ($userReader->isSigned() !== null) {
            $signed = new TicketMessageAttribute('signed');
            $signed->setValue($this->reader->isSigned());
            $ticketMessage->addAttribute($signed);
        }
        if ($userReader->getDecryptedMail() !== null) {
            $decrypted = new TicketMessageAttribute('decrypted');
            $decrypted->setValue(true);
            $ticketMessage->addAttribute($decrypted);
        }
        if ($userReader->getDecryptionError() !== null) {
            $decryptionError = new TicketMessageAttribute('decryption_error');
            $decryptionError->setValue($this->reader->getDecryptionError());
            $ticketMessage->addAttribute($decryptionError);
        }

        if ($this->reader->getProperty('email_source')) {
            $ticketMessage->email_source = $this->reader->getProperty('email_source');
        }

        $ticket->addMessage($ticketMessage);

        // Add agent reply if there was one
        $agentTicketMessage = null;
        if ($agentReply) {
            $this->logMessage('[TicketGatewayProcessor] Adding agent reply');

            $agentTicketMessage = new TicketMessage($this->reader->getId());
            $agentTicketMessage->date_created->modify('+1 second');
            $agentTicketMessage->person = $this->person;
            $agentTicketMessage->setMessageHtml($agentReply);
            $agentTicketMessage->creation_system = 'gateway.agent';

            if (isset($this->ticket_email->reply_actions['is_reply'])) {
                $this->logMessage('is_reply flag is set');
                $agentTicketMessage->is_agent_note = false;
            } elseif (isset($this->ticket_email->reply_actions['is_note'])) {
                $this->logMessage('is_note flag is set');
                $agentTicketMessage->is_agent_note = true;
            } elseif (App::getSetting('core_tickets.email_fwd_reply_as_note')) {
                $this->logMessage('email_fwd_reply_as_note is enabled');
                $agentTicketMessage->is_agent_note = true;
            } else {
                $this->logMessage('email_fwd_reply_as_note is NOT enabled');
                $agentTicketMessage->is_agent_note = false;
            }

            $ticket->addMessage($agentTicketMessage);
            if ($agentTicketMessage->is_agent_note) {
                $ticket->setStatus('awaiting_agent');
            } else {
                $ticket->setStatus('awaiting_user');
            }
        }

        $processedBlobs    = [];
        $processedBlobsCid = [];
        foreach ($userReader->getAttachments() as $attach) {
            $blob = App::getContainer()->getBlobStorage()->createBlobRecordFromString(
                $attach->getFileContents(),
                $attach->getFileName(),
                $attach->getMimeType()
            );

            $processedBlobs[$blob->id] = $blob;

            if ($attach->getContentId()) {
                $processedBlobsCid[$attach->getContentId()] = $blob;
            }
        }

        foreach ($processedBlobs as $blob) {
            $attach           = new TicketAttachment();
            $attach['blob']   = $blob;
            $attach['person'] = $ticket->person;

            if (isset($this->inline_blobs[$blob->id])) {
                $attach->is_inline = true;
            }

            $ticketMessage->addAttachment($attach);
            $blob->is_temp = false;
            App::getOrm()->persist($blob);
        }

        foreach ($this->processBlobs($has_eml_attach) as $blob) {
            $attach           = new TicketAttachment();
            $attach['blob']   = $blob;
            $attach['person'] = $agentTicketMessage ? $agentTicketMessage->person : $ticket->person;

            if (isset($this->inline_blobs[$blob->id])) {
                $attach->is_inline = true;
            }

            if ($agentTicketMessage) {
                $agentTicketMessage->addAttachment($attach);
            } else {
                $ticketMessage->addAttachment($attach);
            }

            $blob->is_temp = false;
            App::getOrm()->persist($blob);
        }

        $this->logMessage('[TicketGatewayProcessor] (ProcessAgentFwd) runNewForwardedEmailAsAttachTicket :: Checking for dupe message: '.$ticketMessage->getMessageHash());
        if ($dupeMessage = App::getOrm()->getRepository(TicketMessage::class)->checkDupeMessage($ticketMessage, null, 10800, $this->getLogger())) {
            $this->setError('duplicate_message');
            $this->logMessage('[TicketGatewayProcessor] (ProcessAgentFwd) runNewForwardedEmailAsAttachTicket :: duplicate message '.$dupeMessage->getId());

            $message = App::getMailer()->createMessage();
            $message->setSuppressAutoreplies(true);
            $message->setTemplate('DeskPRO:emails_agent:error-dupe-forward.html.twig', [
                'subject'       => $this->reader->getSubject()->getSubjectUtf8(),
                'name'          => $this->reader->getFromAddress()->getName() ?: $this->reader->getFromAddress()->getEmail(),
                'error'         => $this->error,
                'old_ticket_id' => $dupeMessage->ticket->id,
            ]);
            $message->setTo($this->reader->getFromAddress()->getEmail());
            $message->attach(\Swift_Attachment::newInstance(
                $this->reader->getRawSource(),
                'message.eml',
                'message/rfc822'
            ));

            App::$container->get('mailer.utils')->sendWithPersonContext($message, $this->person);

            return;
        }

        //------------------------------
        // Reply actions
        //------------------------------

        if ($this->ticket_email->reply_actions) {
            $replyActionsApply           = new ReplyActionsApplicator($this->ticket_email->reply_actions, App::getContainer());
            $replyActionsContext         = new ReplyActionsContext();
            $replyActionsContext->ticket = $ticket;
            if ($agentTicketMessage) {
                $replyActionsContext->message = $agentTicketMessage;
            } else {
                $replyActionsContext->message = $ticketMessage;
            }
            $replyActionsApply->apply($replyActionsContext);
        }

        //------------------------------
        // Process new ticket
        //------------------------------

        App::getDb()->beginTransaction();

        try {
            $this->getTicketManager()->saveTicket($ticket, $executorContext);
            $this->logMessage('[TicketGatewayProcessor] Created ticket '.$ticket['id']);

            App::getDb()->commit();
        } catch (\Exception $e) {
            App::getDb()->rollback();
            throw $e;
        }

        return [
            'via'                  => 'fwd',
            'ticket'               => $ticket,
            'ticket_message'       => $ticketMessage,
            'agent_ticket_message' => $agentTicketMessage,
            'user_ticket_message'  => $ticketMessage,
        ];
    }

    /**
     * @param string $text
     *
     * @return string
     */
    private function cleanBodyText($text)
    {
        if ($this->reader->isOutlookMailer()) {
            $text = Strings::standardEol($text);
            $text = str_replace("\n\n", "\n", $text);
        }

        return $text;
    }
}

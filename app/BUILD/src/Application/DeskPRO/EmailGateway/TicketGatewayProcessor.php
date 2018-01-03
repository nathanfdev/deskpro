<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2017, DeskPRO Ltd.
 *
 * The license agreement under which this software is released
 * can be found at https://www.deskpro.com/eula/
 *
 * By using this software, you acknowledge having read the license
 * and agree to be bound thereby.
 *
 * Please note that DeskPRO is not free software. We release the full
 * source code for our software because we trust our users to pay us for
 * the huge investment in time and energy that has gone into both creating
 * this software and supporting our customers. By providing the source code
 * we preserve our customers' ability to modify, audit and learn from our
 * work. We have been developing DeskPRO since 2001, please help us make it
 * another decade.
 *
 * Like the work you see? Think you could make it better? We are always
 * looking for great developers to join us: http://www.deskpro.com/jobs/
 *
 * ~ Thanks, Everyone at Team DeskPRO
 */

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\EmailGateway;

use Application\DeskPRO\App;
use Application\DeskPRO\EmailGateway\Cutter\ForwardCutter;
use Application\DeskPRO\EmailGateway\Ticket\BounceDetector as TicketBounceDetector;
use Application\DeskPRO\EmailGateway\Ticket\CodeTicketDetector;
use Application\DeskPRO\EmailGateway\Ticket\CompositeDetector;
use Application\DeskPRO\EmailGateway\Ticket\Dp3Detector;
use Application\DeskPRO\EmailGateway\Ticket\SubjectMatchDetector;
use Application\DeskPRO\EmailGateway\Ticket\SubjectRefMatchDetector;
use Application\DeskPRO\EmailGateway\TicketGateway\AgentReplyCodes;
use Application\DeskPRO\EmailGateway\TicketGateway\ProcessAgentFwd;
use Application\DeskPRO\EmailGateway\TicketGateway\ProcessNew;
use Application\DeskPRO\EmailGateway\TicketGateway\ProcessReply;
use Application\DeskPRO\EmailGateway\TicketGateway\TicketIncomingEmail;
use Application\DeskPRO\Entity\EmailSource;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\TmpData;
use Application\DeskPRO\Settings\EmailAccountsSettings;
use Orb\Types\NoValue;
use Orb\Util\Dates;

class TicketGatewayProcessor extends AbstractGatewayProcessor
{
    /**
     * @var string
     */
    protected $error_type;

    /**
     * @var string
     */
    protected $error;

    /**
     * @var string
     */
    protected $source_info;

    /**
     * @var string
     */
    protected $created_object_type;

    /**
     * @var int
     */
    protected $created_object_id;

    /**
     * @var null|array
     */
    protected $created_object_info = null;

    /**
     * @throws \Exception
     *
     * @return \Application\DeskPRO\Entity\Ticket|\Application\DeskPRO\Entity\TicketMessage|null
     */
    public function run()
    {
        //-------------------------
        // Run detectors to see if its a reply
        //-------------------------

        $ticket     = null;
        $person     = null;
        $tacPerson  = null;
        $isBounce   = false;
        $isDp3Reply = false;
        $isPtac     = false;

        $canAddNewPerson = false;

        $bounceDetector = new TicketBounceDetector($this->reader, $this->getEm());
        $bounceDetector->setLogger($this->logger);

        if ($bounceDetector->isBounced()) {
            $isBounce = true;

            $this->logMessage('Is bounced');
            $ticket          = $bounceDetector->getGuessedTicket();
            $canAddNewPerson = true;
            $isPtac          = $bounceDetector->isPublicTac();
        }

        if (!$ticket) {
            $ticketDetect = $this->createTicketDetector();
            if ($isBounce) {
                $ticketDetect->enableBouncedMode();
            }

            $ticket = $ticketDetect->findExistingTicket($this->reader);
            $isPtac = $ticketDetect->isPublicTac();
            $this->logMessage('[TicketGatewayProcessor] Ticket Detector -- Ticket: '.($ticket ? $ticket->id : 'none'));

            $tacPerson = $ticketDetect->findTacPerson($this->reader);
            $this->logMessage('[TicketGatewayProcessor] Ticket Detector -- TAC Person: '.($tacPerson ? $tacPerson->id.' '.$tacPerson->getDisplayContact() : 'none'));

            if ($ticket) {
                $person = $ticketDetect->findExistingPerson($ticket, $this->reader);
                $this->logMessage('[TicketGatewayProcessor] Ticket Detector -- Person: '.($person ? $person->id.' '.$person->getDisplayContact() : 'none'));

                $canAddNewPerson = $ticketDetect->canAddUnknownPerson($ticket, $this->reader);

                if ($ticketDetect->getMatchedDetector($this->reader) instanceof Dp3Detector) {
                    $isDp3Reply = true;
                }
            }
        }

        //-------------------------
        // Check TACs
        //-------------------------

        // If this was a reply via a TAC, then the person detected via address and the person who owns the TAC
        // should be the sames. Otherwise, *probably* means the agent used a different email address.
        if ($tacPerson && $tacPerson->is_agent) {
            // Need to look up the From sender manually because we dont know which ticket dector was used above,
            // and the way they find existing people is an implementation detail we dont know here
            $existPerson = $this->container->getEm()->getRepository(Person::class)->findOneByEmail($this->reader->getFromAddress()->email);

            if (($existPerson && $tacPerson !== $existPerson) || !$existPerson) {
                $this->logMessage('Agent email with TAC from unknown email address. Re-running ticket detectors without code detector.');

                $ticket          = null;
                $person          = null;
                $tacPerson       = null;
                $isDp3Reply      = false;
                $canAddNewPerson = false;

                // Invalid or mis-matching auth-code, we will re-run the detectors without TAC matching
                $ticketDetect = $this->createTicketDetector(false);
                if ($isBounce) {
                    $ticketDetect->enableBouncedMode();
                }

                $ticket = $ticketDetect->findExistingTicket($this->reader);
                $this->logMessage('[TicketGatewayProcessor] [RERUN] Ticket Detector -- Ticket: '.($ticket ? $ticket->id : 'none'));

                $tacPerson = $ticketDetect->findTacPerson($this->reader);
                $this->logMessage('[TicketGatewayProcessor] [RERUN] Ticket Detector -- TAC Person: '.($tacPerson ? $tacPerson->id.' '.$tacPerson->getDisplayContact() : 'none'));

                if ($ticket) {
                    $person = $ticketDetect->findExistingPerson($ticket, $this->reader);
                    $this->logMessage('[TicketGatewayProcessor] [RERUN] Ticket Detector -- Person: '.($person ? $person->id.' '.$person->getDisplayContact() : 'none'));

                    $canAddNewPerson = $ticketDetect->canAddUnknownPerson($ticket, $this->reader);
                }
            }
        }

        //-------------------------
        // Rate limit
        //-------------------------

        $rateLimit    = $this->container->getSetting('core.emails.rate_count', EmailAccountsSettings::DEFAULT_RATE_COUNT);
        $rateTime     = $this->container->getSetting('core.emails.rate_time', EmailAccountsSettings::DEFAULT_RATE_TIME);
        $rateLocktime = $this->container->getSetting('core.emails.rate_locktime', EmailAccountsSettings::DEFAULT_RATE_LOCK_TIME);
        $realFrom     = $this->reader->getRealFromAddress()->getEmail();
        /** @var \Application\DeskPRO\EntityRepository\EmailSource $sourceRepos */
        $sourceRepos = $this->container->getEm()->getRepository(EmailSource::class);

        if ($rateLimit && !($tacPerson && $tacPerson->is_agent)) {
            $isRateReject = false;

            if ($sourceRepos->isEmailAddressRateLimited($realFrom, $rateLocktime)) {
                $isRateReject = true;
                $this->logMessage('Rate limited -- currently locked out');
            } elseif ($sourceRepos->countEmailsWithinTime($realFrom, $rateTime) >= $rateLimit) {
                $isRateReject = true;
                $this->logMessage('Rate limited -- this is the first message over the threshold');

                $message = $this->container->getMailer()->createMessage();
                $message->setTemplate('DeskPRO:emails_user:rate-limit-notice.html.twig', [
                    'ticket'        => $ticket,
                    'subject'       => $this->reader->getSubject()->getSubjectUtf8(),
                    'name'          => $this->reader->getFromAddress()->getName() ?: $this->reader->getFromAddress()->getEmail(),
                    'num_messagess' => $rateLimit,
                    'time_limit'    => Dates::secsToReadable($rateTime),
                    'time_lock'     => Dates::secsToReadable($rateLocktime),
                    'date_lock_end' => date($this->container->getSetting('core.date_time'), time() + $rateLocktime),
                ]);
                $message->setTo($this->reader->getFromAddress()->getEmail());

                $lang = $person ? $person->getLanguage() : null;
                if ($lang) {
                    $this->container->getTranslator()->setTemporaryLanguage($lang, function () use ($message) {
                        $message->prepare();
                    });
                } else {
                    $message->prepare();
                }

                $this->container->getMailer()->send($message);
            }

            if ($isRateReject) {
                $this->logMessage('Rate limited, message is rejected');
                $this->error      = 'rate_limit';
                $this->error_type = 'rejected';

                return null;
            }
        }

        //-------------------------
        // Check if we should create a new user on the ticket
        //-------------------------

        if ($ticket and !$person and $canAddNewPerson) {
            $this->logMessage(sprintf('[TicketGatewayProcessor] Could not find user on ticket, adding user with email %s', $this->reader->getFromAddress()->getEmail()));

            $personProcessor = new PersonFromEmailProcessor();

            // If the detector didnt find a person, doesnt mean they dont exist
            $person = $personProcessor->findPerson($this->reader->getFromAddress());

            // But we'll create them now if they dont
            if (!$person) {
                $this->logMessage('[TicketGatewayProcessor] No existing person found, will try and create it');
                $person = $personProcessor->createPerson($this->reader->getFromAddress());
                $this->logMessage('[TicketGatewayProcessor] Person ID is '.$person->id);
            }

            if ($person && !$person->is_agent && !$isBounce) {
                $ticket->addParticipantPerson($person);
            }
        }

        //-------------------------
        // Reject agent bounces
        //-------------------------

        if ($person && $person->is_agent && $isBounce) {
            $this->logMessage('[TicketGatewayProcessor] Is an agent message and is detected as bounced. Rejecting message.');
            $this->error      = 'agent_bounce';
            $this->error_type = 'rejected';

            return null;
        }

        //-------------------------
        // Reject new tickets by disabled users
        //-------------------------

        if ($person && ($person->is_disabled || $person->is_deleted)) {
            //-------------------------
            // Reject new replies by disabled users
            //-------------------------

            if (!$person->is_agent && $person->is_disabled) {
                // user is disabled so can't create/reply to tickets

                if (!$this->reader->isFromRobot() && !$isBounce) {
                    if ($this->container->get('deskpro.feature_flags')->hasBeta('email_templates')) {
                        $viewModel = $this->container->get('email.user_viewmodel_factory')
                            ->createAccountDisabledModel($ticket);
                        $message = $this->container->get('email.email_sender')
                            ->prepareMessage($viewModel, ['to' => $this->reader->getFromAddress()->getEmail()]);
                    } else {
                        $message = $this->container->getMailer()->createMessage();
                        $message->setTemplate(
                            'DeskPRO:emails_user:account-disabled.html.twig',
                            [
                                'subject' => $this->reader->getSubject()->getSubjectUtf8(),
                                'ticket'  => ['subject' => $this->reader->getSubject()->getSubjectUtf8()],
                                'name'    => $this->reader->getFromAddress()->getName() ?: $this->reader->getFromAddress(
                                )->getEmail(),
                            ]
                        );
                        $message->setTo($this->reader->getFromAddress()->getEmail());
                    }
                    $this->container->getTranslator()->setTemporaryLanguage(
                        $person->getLanguage(),
                        function () use ($message) {
                            $message->prepare();
                        }
                    );

                    $this->container->getMailer()->send($message);
                }
            }
            $this->logMessage('[TicketGatewayProcessor] User is disabeld, rejecting message');
            $this->error      = 'from_disabled_user';
            $this->error_type = 'rejected';

            return null;
        }

        //-------------------------
        // Handle reply to resolved tickets
        //-------------------------

        $replyAsNew = false;

        if ($ticket and $person and !$person->is_agent and $ticket->status == 'resolved' and !$person->hasPerm('tickets.reopen_resolved')) {
            $this->logMessage('[TicketGatewayProcessor] Ticket is resolved');

            if ($person->hasPerm('tickets.reopen_resolved_createnew')) {
                $this->logMessage('[TicketGatewayProcessor] Has perm reopen_resolved_createnew so creating a new ticket');
                $ticket     = null;
                $replyAsNew = true;
            } else {
                $this->logMessage('[TicketGatewayProcessor] Message is being rejected because ticket is resolved');

                if ($this->account_email_address) {
                    $emailTo = $this->account_email_address;
                } else {
                    $emailTo = $this->account->getUseEmailAddress();
                }

                $fromAddress = $this->container->getEmailAccountManager()->getAccountForTicket($ticket)->getUseEmailAddress();

                // user is disabled so can't create/reply to tickets
                if (!$this->reader->isFromRobot() && !$isBounce) {
                    if ($this->container->get('deskpro.feature_flags')->hasBeta('email_templates')) {
                        $viewModel = $this->container->get('email.user_viewmodel_factory')
                            ->createNewReplyRejectResolvedModel($ticket);
                        $this->container->get('email.email_sender')
                            ->send($viewModel, ['to' => $this->reader->getFromAddress()->getEmail()]);
                    } else {
                        $message = $this->container->getMailer()->createMessage();
                        $message->setTemplate('DeskPRO:emails_user:new-reply-reject-resolved.html.twig', [
                            'subject'  => $this->reader->getSubject()->getSubjectUtf8(),
                            'name'     => $this->reader->getFromAddress()->getName() ?: $this->reader->getFromAddress()->getEmail(),
                            'ticket'   => $ticket,
                            'person'   => $person,
                            'email_to' => $emailTo,
                        ]);
                        $message->setTo($this->reader->getFromAddress()->getEmail());
                        $message->setFrom($fromAddress);
                        $message->attach(\Swift_Attachment::newInstance(
                            $this->reader->getRawSource(),
                            'message.eml',
                            'message/rfc822'
                        ));

                        $this->container->getTranslator()->setTemporaryLanguage($person->getLanguage(), function () use ($message) {
                            $message->prepare();
                        });

                        $this->container->getMailer()->send($message);
                    }
                }

                $this->error      = 'obj_closed';
                $this->error_type = 'rejected';

                return null;
            }
        }

        //-------------------------
        // Detect agent reply codes
        //-------------------------

        $replyActions  = null;
        $emailBodyHtml = $this->reader->getBodyHtml()->getBodyUtf8();
        $emailBodyText = $this->reader->getBodyText()->getBodyUtf8();

        if ($person) {
            $checkPerson = $person;
        } elseif ($this->reader->getFromAddress()) {
            $checkPerson = $this->container->getSystemService('AgentData')->getByEmail($this->reader->getFromAddress()->getEmail());
        } else {
            $checkPerson = null;
        }

        if ($checkPerson && $checkPerson->is_agent) {
            if ($emailBodyHtml) {
                $this->logMessage('Checking for agent reply codes in HTML body');
                $replyCodes = new AgentReplyCodes($emailBodyHtml, true);
                $replyCodes->setCleaner($this->container->getInputCleaner());
                $replyCodes->setLogger($this->logger);

                $replyActions = $replyCodes->getProperties();
                if ($replyActions) {
                    $emailBodyHtml = $replyCodes->getNewBody();
                }
            }

            $this->logMessage('Checking for agent reply codes in TEXT body');
            $replyCodes = new AgentReplyCodes($emailBodyText, false);
            $replyCodes->setLogger($this->logger);

            $txtReplyActions = $replyCodes->getProperties();
            if ($txtReplyActions) {
                $emailBodyText = $replyCodes->getNewBody();

                if ($replyActions) {
                    $this->logMessage('TEXT body has codes, but we are using HTML email so they will be ignored');
                } else {
                    $replyActions = $txtReplyActions;
                }
            }
        }

        //-------------------------
        // Unset ticket if its an agent fwd
        //-------------------------

        if ($ticket and $this->container->getSetting('core_tickets.process_agent_fwd') and $person['is_agent'] and ForwardCutter::subjectIsForward($this->reader->getSubject()->subject)) {
            $this->logMessage(sprintf('Found a ticket match #%d but this is an agent fwd so unsetting', $ticket->getId()));
            $ticket = null;
        }

        //-------------------------
        // Create ticket email obj
        //-------------------------

        $ticketEmail                  = new TicketIncomingEmail($this);
        $ticketEmail->reader          = $this->reader;
        $ticketEmail->ticket          = $ticket;
        $ticketEmail->person          = $person;
        $ticketEmail->tac_person      = $tacPerson;
        $ticketEmail->is_bounce       = $isBounce;
        $ticketEmail->email_body_html = $emailBodyHtml;
        $ticketEmail->email_body_text = $emailBodyText;
        $ticketEmail->is_dp3_reply    = $isDp3Reply;
        $ticketEmail->reply_actions   = $replyActions;
        $ticketEmail->isPublicTac     = $isPtac;

        if ($ticket && $person) {
            $this->container->getTicketManager()->markAsManaged($ticket);

            return $this->runReply($ticketEmail);
        } else {
            return $this->runNew($ticketEmail, $replyAsNew);
        }
    }

    /**
     * Create a publicly visible reply to a ticket from an email.
     *
     * @param TicketIncomingEmail $ticket_email
     *
     * @return \Application\DeskPRO\Entity\TicketMessage|null
     */
    private function runReply(TicketIncomingEmail $ticket_email)
    {
        $ticket = $ticket_email->ticket;
        $person = $ticket_email->person;

        $person_processor = new PersonFromEmailProcessor();
        $person_processor->passPerson($this->reader->getFromAddress(), $person);

        if ($this->reader->getHeader('X-DeskPRO-Build') && $this->reader->getHeader('X-DeskPRO-Build')->getHeader()) {
            $this->logMessage('[TicketGatewayProcessor] Detected a DeskPRO reply, disabling disable_autoresponses');
            $person->setDisableAutoresponses(
                true,
                'User detected as a DeskPRO helpdesk'
            );
        }

        if (!$person->disable_autoresponses) {
            if ($return_path = $this->reader->getHeader('Return-Path')) {
                if ($return_path->getHeader() == '<>') {
                    $this->logMessage('Null return path, disabling auto-responses for this user');
                    $person->setDisableAutoresponses(
                        true,
                        'Client sent a null Return-Path'
                    );
                }
            }
        }

        App::setCurrentPerson($person);

        // If the agent is replying to an email that is not a notification, then this check doesnt
        // need to run (e.g., they replied to an email they were CCd on).
        $is_reply_to_dpmail = false;
        if ($body_html = $ticket_email->email_body_html) {
            if (
                strpos($body_html, 'DP_BOTTOM_MARK') !== false
                || strpos($body_html, 'DP_TOP_MARK') !== false
                || strpos($body_html, 'DP_MESSAGE_BEGIN') !== false
                || strpos($body_html, 'DP_USER_EMAIL') !== false
                || strpos($body_html, 'DP_AGENT_EMAIL') !== false
            ) {
                $is_reply_to_dpmail = true;
            }
        }

        if ($is_reply_to_dpmail) {
            $this->logMessage('[TicketGatewayProcessor] IS a reply to a DeskPRO email');
        } else {
            $this->logMessage('[TicketGatewayProcessor] NOT a reply to a DeskPRO email');
        }

        // todo injection
        $translator = $this->container->getTranslator();
        $reply_proc = new ProcessReply($ticket, $person, $ticket_email, $translator);
        $reply_proc->setLogger($this->logger);

        if (
            $person['is_agent']
            &&
            !$ticket_email->isPublicTac
            && (
                // Is not a user email
                (strpos($ticket_email->email_body_html, 'DP_USER_EMAIL') === false && $is_reply_to_dpmail)
                ||
                // Or is a text email where user email markers wouldnt be detected
                ($ticket_email->tac_person && $ticket_email->tac_person->getId() == $person->getId() && !$is_reply_to_dpmail)
            )
        ) {
            $this->logMessage('[TicketGatewayProcessor] runNewAgentReply');
            $obj = $reply_proc->run('agent');
        } else {
            $this->logMessage('[TicketGatewayProcessor] runNewUserReply');
            $obj = $reply_proc->run('user');
        }

        if ($err = $reply_proc->getError()) {
            $this->error      = $err;
            $this->error_type = $reply_proc->getErrorType();

            return null;
        }

        if (NoValue::is($obj)) {
            $this->created_object_type = 'no_value';
            $this->created_object_id   = 0;
        } else {
            $this->created_object_type = 'ticket_message';
            $this->created_object_id   = $obj->id;
            $this->created_object_info = [
                'ticket_id'         => $obj->ticket->id,
                'ticket_message_id' => $obj->id,
            ];
        }

        return $obj;
    }

    /**
     * @param TicketIncomingEmail $ticket_email
     * @param bool                $reply_as_new
     *
     * @return \Application\DeskPRO\Entity\Ticket|null
     */
    private function runNew(TicketIncomingEmail $ticket_email, $reply_as_new = false)
    {
        $this->logMessage('[TicketGatewayProcessor] Creating new ticket');

        $person_processor = new PersonFromEmailProcessor();
        $person           = $person_processor->findPerson($this->reader->getFromAddress());

        if ($person) {
            $this->logMessage('[TicketGatewayProcessor] Found existing person: '.$person['id']);
            $person_processor->passPerson($this->reader->getFromAddress(), $person);
        } else {
            if ($this->container->getSetting('core.reg_enabled')) {
                $person = $person_processor->createPerson($this->reader->getFromAddress());
                $this->logMessage('[TicketGatewayProcessor] Created new contact: '.$person['id']);
            }
        }

        // Still no person means reg is closed
        if (!$person) {
            $this->logMessage('[TicketGatewayProcessor] No user and closed registration');
            $this->error      = EmailSource::ERR_PERM_INSUFFICIENT;
            $this->error_type = 'rejected';

            $account_manager = $this->container->getEmailAccountManager();
            $user_email      = $this->reader->getFromAddress()->getEmail();

            if (!$ticket_email->is_bounce && !$this->reader->isFromRobot() && !$account_manager->findAccountForEmailAddress($user_email)) {
                if ($this->container->get('deskpro.feature_flags')->hasBeta('email_templates')) {
                    $viewModel = $this->container->get('email.user_viewmodel_factory')
                        ->createNewTicketRegClosedModel($this->reader->getSubject()->getSubjectUtf8());
                    $this->container->get('email.email_sender')
                        ->send($viewModel, ['to' => $this->reader->getFromAddress()->getEmail()]);
                } else {
                    $message = $this->container->getMailer()->createMessage();
                    $message->setTemplate('DeskPRO:emails_user:new-ticket-reg-closed.html.twig', [
                        'subject' => $this->reader->getSubject()->getSubjectUtf8(),
                        'ticket'  => ['subject' => $this->reader->getSubject()->getSubjectUtf8()],
                        'name'    => $this->reader->getFromAddress()->getName() ?: $this->reader->getFromAddress()->getEmail(),
                    ]);
                    $message->setTo($this->reader->getFromAddress()->getEmail());
                    $this->container->getMailer()->send($message);
                }
            }

            return null;
        }

        //-------------------------
        // Handle validation
        //-------------------------

        if ($person && !$person->isConfirmed() && $this->container->getSetting('core_tickets.email_require_validation')) {
            $this->logMessage('User is not confirmed, message is rejected');

            $email_address = $person->findEmailAddress($this->reader->getFromAddress()->getEmail()) ?: $person->getPrimaryEmail();

            /** @var EmailSource $source */
            if (($source = $this->options['email_source']) && $email_address) {
                $tmpdata = TmpData::create('newticket_email_validate', [
                    'email_source_id' => $source->getId(),
                    'person_email_id' => $email_address->getId(),
                ]);

                $this->container->getEm()->persist($tmpdata);
                $this->container->getEm()->flush($tmpdata);

                if (!$this->reader->isFromRobot() && !$ticket_email->is_bounce) {
                    $this->container->get('portal_validation')->sendTicketByEmailVerificationEmail(
                        $person,
                        $this->reader,
                        $tmpdata->getCode()
                    );
                    $this->logMessage('--> User was sent validation link');
                } else {
                    $this->logMessage('--> Bounce or robot detected no validation link sent');
                }

                $this->error      = EmailSource::ERR_USER_VALIDATING;
                $this->error_type = EmailSource::STATUS_REJECTED_SOFT;
            } else {
                $this->logMessage('--> This is perm and use was not notified. There was no SOURCE email with this request, meaning no email link can be sent to the user to retry.');
                $this->error      = EmailSource::ERR_USER_VALIDATING;
                $this->error_type = EmailSource::STATUS_REJECTED;
            }

            return null;
        }

        if ($person) {
            $this->logMessage('[TicketGatewayProcessor] Found existing person: '.$person['id']);
            $person_processor->passPerson($this->reader->getFromAddress(), $person);
        } else {
            $this->logMessage('[TicketGatewayProcessor] Creating new contact');
        }

        if ($person && $person->is_agent && $ticket_email->is_bounce) {
            $this->logMessage('[TicketGatewayProcessor] Is an agent message and is detected as bounced. Rejecting message.');
            $this->error      = 'agent_bounce';
            $this->error_type = 'rejected';

            return null;
        }

        if ($person && ($person->is_disabled || $person->is_deleted)) {
            $this->logMessage('[TicketGatewayProcessor] User is disabeld, rejecting message');
            $this->error      = 'from_disabled_user';
            $this->error_type = 'rejected';

            return null;
        }

        if ($this->reader->getHeader('X-DeskPRO-Build') && $this->reader->getHeader('X-DeskPRO-Build')->getHeader()) {
            $this->logMessage('[TicketGatewayProcessor] Detected a DeskPRO reply, disabling disable_autoresponses');
            $person->setDisableAutoresponses(
                true,
                'User detected as a DeskPRO helpdesk'
            );
        }

        if (!$person->disable_autoresponses) {
            if ($return_path = $this->reader->getHeader('Return-Path')) {
                if ($return_path->getHeader() == '<>') {
                    $this->logMessage('Null return path, disabling auto-responses for this user');
                    $person->setDisableAutoresponses(
                        true,
                        'Client sent a null Return-Path'
                    );
                }
            }
        }

        App::setCurrentPerson($person);

        if ($this->container->getSetting('core_tickets.process_agent_fwd') and $person->is_agent and ForwardCutter::subjectIsForward($this->reader->getSubject()->subject)) {
            $this->logMessage('[TicketGatewayProcessor] runNewForwardedTicket');

            $fwd_proc = new ProcessAgentFwd($this->account, $person, $ticket_email);
            $fwd_proc->setLogger($this->logger);

            $created = $fwd_proc->run();

            if ($err = $fwd_proc->getError()) {
                $this->error      = $err;
                $this->error_type = 'rejected';

                return null;
            }

            $this->created_object_type = 'ticket';
            $this->created_object_id   = $created['ticket']->id;
            $this->created_object_info = [
                'ticket_id'         => $created['ticket']->id,
                'ticket_message_id' => $created['ticket_message']->id,
            ];

            return $created['ticket'];
        } else {
            $this->logMessage('[TicketGatewayProcessor] runNewTicket');

            $ticket_email->force_reply_cutter = $reply_as_new;

            $translator = $this->container->getTranslator();
            $new_proc   = new ProcessNew($this->account, $person, $ticket_email, $translator);
            $new_proc->setLogger($this->logger);

            $created = $new_proc->run();

            if ($err = $new_proc->getError()) {
                $this->error      = $err;
                $this->error_type = $new_proc->getErrorType();

                return null;
            }

            $this->created_object_type = 'ticket';
            $this->created_object_id   = $created['ticket']->id;
            $this->created_object_info = [
                'ticket_id'         => $created['ticket']->id,
                'ticket_message_id' => $created['ticket_message']->id,
            ];

            return $created['ticket'];
        }
    }

    /**
     * @param bool $with_code_check
     *
     * @return CompositeDetector
     */
    private function createTicketDetector($with_code_check = true)
    {
        $ticket_detect = new CompositeDetector();
        $ticket_detect->setLogger($this->logger);

        if ($with_code_check) {
            $ticket_detect->addDetector(new CodeTicketDetector());
        }

        if ($this->container->getSetting('core.deskpro3importer')) {
            $ticket_detect->addDetector(new Dp3Detector());
        }

        $ticket_detect->addDetector(new SubjectRefMatchDetector());

        if ($this->container->getSetting('core_tickets.gateway_enable_subject_match')) {
            $m = new SubjectMatchDetector();

            if ($this->account && $this->container->getSetting('core_tickets.enable_same_account_subject_matching')) {
                $m->enableSameAccountSubjectMatching($this->account);
            }
            if ($this->container->getSetting('core_tickets.enable_exact_subject_matching')) {
                $m->enableExactSubjectMatching();
            }

            $ticket_detect->addDetector($m);
        }

        return $ticket_detect;
    }

    /**
     * {@inheritdoc}
     */
    public function getErrorCode()
    {
        return $this->error;
    }

    /**
     * 'error' or 'rejected'.
     *
     * @return string
     */
    public function getErrorType()
    {
        return $this->error_type;
    }

    /**
     * {@inheritdoc}
     */
    public function getSourceInfo()
    {
        if ($this->source_info) {
            $messages = is_array($this->source_info) ? $this->source_info : [$this->source_info];
        } else {
            $messages = [];
        }

        return $messages;
    }

    /**
     * @return int
     */
    public function getCreatedObjectId()
    {
        return $this->created_object_id;
    }

    /**
     * @return string
     */
    public function getCreatedObjectType()
    {
        return $this->created_object_type;
    }

    /**
     * @return array|null
     */
    public function getCreatedObjectInfo()
    {
        return $this->created_object_info;
    }
}

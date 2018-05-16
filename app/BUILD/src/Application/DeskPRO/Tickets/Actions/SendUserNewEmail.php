<?php

/**
 * DeskPRO.
 *
 * @category Tickets
 */

namespace Application\DeskPRO\Tickets\Actions;

use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Entity\TicketMessage;
use Application\DeskPRO\Entity\TicketTrigger;
use Application\DeskPRO\Tickets\ExecutorContextInterface;
use Application\DeskPRO\Tickets\TicketEmail;
use Application\DeskPRO\Tickets\TicketEmailBuilder;
use Orb\Util\CheckedOptionsArray;

/**
 * Send an email to the user.
 *
 * @option bool template       The template to send
 * @option bool from_name      Who to send the email from
 * @option bool from_account   The account to send from (falsey for ticket account)
 * @option bool do_cc_users    True to CC the email to other user parts in the ticket
 */
class SendUserNewEmail extends AbstractEmailAction
{
    /**
     * {@inheritdoc}
     */
    protected function getOptionsDef()
    {
        $options = new CheckedOptionsArray();
        $options->addValidNames('template', 'from_name', 'from_account', 'do_cc_users', 'headers');

        return $options;
    }

    /**
     * {@inheritdoc}
     */
    public function applyAction(Ticket $ticket, ExecutorContextInterface $context)
    {
        $context->getLogger()->debug('[SendUserNewEmail] Begin');
        $startTime = microtime(true);

        try {
            $fromAccount = $this->getFromEmailAccountOption($ticket, $context);
        } catch (\InvalidArgumentException $e) {
            $context->getLogger()->warn("[SendUserNewEmail] Error {$e->getMessage()}");

            return;
        }

        try {
            $template = $this->getEmailTemplateOption($ticket, $context, false);
        } catch (\InvalidArgumentException $e) {
            $context->getLogger()->warn("[SendUserNewEmail] Error {$e->getMessage()}");

            return;
        }

        //-------------------------
        // Send emails
        //-------------------------

        $factory = $this->getContainer()->get('email.user_viewmodel_factory');

        $messagesArgs = [
            'template' => $template,
        ];

        switch ($context->getEventType()) {
            case TicketTrigger::EVENT_TYPE_NEWTICKET:
            case TicketTrigger::EVENT_TYPE_UPDATE:
            case 'system':
                $arguments = [$ticket];
                break;
            case TicketTrigger::EVENT_TYPE_NEWREPLY:
                /** @var \Application\DeskPRO\EntityRepository\TicketMessage $messageRepo */
                $messageRepo = $this->getContainer()->getEm()->getRepository(TicketMessage::class);
                $messages    = $messageRepo->getTicketMessages(
                    $ticket,
                    [
                        'with_notes'       => false,
                        'with_attachments' => true,
                        'limit'            => 15,
                        'order'            => 'DESC',
                    ]
                );
                if ($messages) {
                    $lastMessage = array_shift($messages);
                    $arguments   = [$ticket, $lastMessage];
                } else {
                    $context->getLogger()->info('No reply to send: '.$context->getEventType());

                    return;
                }
                break;
            default:
                $context->getLogger()->info('Unknown event type: '.$context->getEventType());

                return;
        }

        $brandStack = $this->getContainer()->getBrandStack();
        if ($ticket->getBrand()) {
            $brandStack->push($ticket->getBrand());
        }

        $viewModel = $this->createViewModelFromTemplate($template, $arguments, $context);

        if (!$viewModel) {
            return;
        }

        $mailer = $this->getContainer()->get('mailer');

        $emailBuilder = TicketEmailBuilder::createFromContainer($this->getContainer())
            ->setTicket($ticket)
            ->setToPerson($ticket->person)
            ->setUserMode()
            ->setTemplateName($template)
            ->setFromName($this->renderFromName($this->getActionOption('from_name'), $ticket, $context, 'user'))
            ->setMaxAttachSize($this->getContainer()->getSetting('core.sendemail_attach_maxsize'))
            ->setLogger($context->getLogger())
            ->setHeaders($this->processHeaders($this->getActionOption('headers', []), $ticket, $context))
            ->setFromEmailAccount($fromAccount)
        ;

        if ($this->getActionOption('do_cc_users')) {
            $emailBuilder->enableUserCc();
        }

        // If this is from a user reply, then mark the email as auto and handle disable auto setting
        if ($context->getEventPerformer() == 'user' && $ticket->getStateChangeRecorder()->hasNewReply()) {
            $context->getLogger()->info('[SendUserNewEmail] Identified as an automatic email');
            $emailBuilder->setIsAuto();

            if ($context->getVars()->has('ticket_email')) {
                /** @var \Application\DeskPRO\EmailGateway\TicketGateway\TicketIncomingEmail $ticketEmail */
                $ticketEmail = $context->getVars()->get('ticket_email');
                if ($ticketEmail->is_bounce) {
                    $context->getLogger()->info('Skipping email because is_bounce = true');

                    return;
                }
            }

            if ($ticket->person->disable_autoresponses) {
                $context->getLogger()->info('Skipping email because user is marked as an auto-responder');

                return;
            }

            foreach ($ticket->getStateChangeRecorder()->getNewUserReplies() as $m) {
                if ($m->person->disable_autoresponses) {
                    $context->getLogger()->info(sprintf('Skipping email because user #%d %s on message #%d is an auto-responder', $m->person->id, $m->person->getDisplayContact(), $m->id));

                    return;
                }
            }
        }

        if ($ticket->getTicketPersonEmail() && $ticket->getTicketPersonEmail()->getPerson()) {
            $toEmail = $ticket->getTicketPersonEmail()->getEmail();
        } else {
            throw new \RuntimeException('no email address');
        }

        $messagesArgs['to'] = $toEmail;

        /** @var TicketEmail $ticketEmail */
        $ticketEmail = $emailBuilder->buildTicketEmail();

        $message = $ticketEmail->prepareMailerMessage([], false);

        $message = $this->getContainer()->get('email.email_sender')
            ->prepareMessage($viewModel, $messagesArgs, $message);

        try {
            $mailer->send($message);
        } catch (\Exception $e) {
            $context->getLogger()->error(
                sprintf('Exception: [%s] %s', $e->getCode(), $e->getMessage()),
                ['exception' => $e]
            );

            throw $e;
        }

        /* If we added a brand in the stack we remove it */
        if ($ticket->getBrand()) {
            $brandStack->pop();
        }

        $context->getLogger()->info(sprintf('[SendUserNewEmail] Sent message in %.3fs', microtime(true) - $startTime));
    }

    /**
     * {@inheritdoc}
     */
    public function isNoop(Ticket $ticket, ExecutorContextInterface $context)
    {
        if (!$this->getContainer()->getEmailAccountManager()->countOutgoingAccounts()) {
            $context->getLogger()->debug('[SendUserNewEmail] no outgoing email accounts are defined');

            return true;
        }

        if ($context->getVars()->get('mute_user_emails')) {
            $context->getLogger()->debug('[SendUserNewEmail] mute_user_emails = true');

            return true;
        }

        if (!$ticket->getPerson()->getPrimaryEmail()) {
            $context->getLogger()->debug("[SendUserNewEmail] person #{$ticket->getPerson()->getId()} has no email");

            return true;
        }

        return false;
    }
}

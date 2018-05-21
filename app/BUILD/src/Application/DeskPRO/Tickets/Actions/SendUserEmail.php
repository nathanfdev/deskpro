<?php

namespace Application\DeskPRO\Tickets\Actions;

use Application\DeskPRO\Entity\Ticket;
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
class SendUserEmail extends AbstractEmailAction
{
    /**
     * {@inheritdoc}
     */
    protected function getOptionsDef()
    {
        $options = new CheckedOptionsArray();
        $options->addValidNames('template', 'from_name', 'from_account', 'do_cc_users', 'do_cc_users_specify', 'headers');

        return $options;
    }

    /**
     * {@inheritdoc}
     */
    public function applyAction(Ticket $ticket, ExecutorContextInterface $context)
    {
        $context->getLogger()->debug('[SendUserEmail] Begin');
        $startTime = microtime(true);

        try {
            $fromAccount = $this->getFromEmailAccountOption($ticket, $context);
        } catch (\InvalidArgumentException $e) {
            $context->getLogger()->warn("[SendUserEmail] Error {$e->getMessage()}");

            return;
        }

        try {
            $template = $this->getEmailTemplateOption($ticket, $context, false);
        } catch (\InvalidArgumentException $e) {
            $context->getLogger()->warn("[SendUserEmail] Error {$e->getMessage()}");

            return;
        }

        //-------------------------
        // Vars
        //-------------------------

        $defaultVars = $this->getStandardEmailVars($ticket, $context, 'user');

        //-------------------------
        // Send emails
        //-------------------------

        $emailBuilder = TicketEmailBuilder::createFromContainer($this->getContainer())
            ->setTicket($ticket)
            ->setToPerson($ticket->getPerson())
            ->setUserMode()
            ->setTemplateName($template)
            ->setFromName($this->renderFromName($this->getActionOption('from_name'), $ticket, $context, 'user'))
            ->setMaxAttachSize($this->getContainer()->getSetting('core.sendemail_attach_maxsize'))
            ->setLogger($context->getLogger())
            ->setHeaders($this->processHeaders($this->getActionOption('headers', []), $ticket, $context))
            ->setFromEmailAccount($fromAccount);

        if ($this->getActionOption('do_cc_users')) {
            $emailBuilder->enableUserCc();
        }

        // If this is from a user reply, then mark the email as auto and handle disable auto setting
        if ($context->getEventPerformer() == 'user' && $ticket->getStateChangeRecorder()->hasNewReply()) {
            $context->getLogger()->info('[SendUserEmail] Identified as an automatic email');
            $emailBuilder->setIsAuto();

            if ($context->getVars()->has('ticket_email')) {
                /** @var \Application\DeskPRO\EmailGateway\TicketGateway\TicketIncomingEmail $ticketEmail */
                $ticketEmail = $context->getVars()->get('ticket_email');
                if ($ticketEmail->is_bounce) {
                    $context->getLogger()->info('Skipping email because is_bounce = true');

                    return;
                }
            }

            if ($ticket->getPerson()->disable_autoresponses) {
                $context->getLogger()->info('Skipping email because user is marked as an auto-responder');

                return;
            }

            foreach ($ticket->getStateChangeRecorder()->getNewUserReplies() as $m) {
                if ($m->getPerson()->disable_autoresponses) {
                    $context->getLogger()->info(sprintf('Skipping email because user #%d %s on message #%d is an auto-responder', $m->getPerson()->getId(), $m->getPerson()->getDisplayContact(), $m->getId()));

                    return;
                }
            }
        }

        $defaultVars = array_merge($defaultVars, $emailBuilder->getCommonVars(false));

        /** @var TicketEmail $ticketEmail */
        $ticketEmail = $emailBuilder->buildTicketEmail();

        try {
            $ticketEmail->send($defaultVars);
            $this->recordEmailTicketLog($ticketEmail, $ticket, $context);
        } catch (\Exception $e) {
            $context->getLogger()->error(
                sprintf('Exception: [%s] %s', $e->getCode(), $e->getMessage()),
                ['exception' => $e]
            );

            throw $e;
        }

        $context->getLogger()->info(sprintf('[SendUserEmail] Sent message in %.3fs', microtime(true) - $startTime));
    }

    /**
     * {@inheritdoc}
     */
    public function isNoop(Ticket $ticket, ExecutorContextInterface $context)
    {
        if (!$this->getContainer()->getEmailAccountManager()->countOutgoingAccounts()) {
            $context->getLogger()->debug('[SendUserEmail] no outgoing email accounts are defined');

            return true;
        }

        if ($context->getVars()->get('mute_user_emails')) {
            $context->getLogger()->debug('[SendUserEmail] mute_user_emails = true');

            return true;
        }

        if (!$ticket->getPerson()->getPrimaryEmail()) {
            $context->getLogger()->debug("[SendUserEmail] person #{$ticket->getPerson()->getId()} has no email");

            return true;
        }

        return false;
    }
}

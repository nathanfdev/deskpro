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

namespace Application\DeskPRO\Tickets\Actions;

use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Tickets\ExecutorContextInterface;
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
class SendUserLegacyEmail extends AbstractEmailAction
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
        $context->getLogger()->debug('[SendUserLegacyEmail] Begin');
        $startTime = microtime(true);

        try {
            $fromAccount = $this->getFromEmailAccountOption($ticket, $context);
        } catch (\InvalidArgumentException $e) {
            $context->getLogger()->warn("[SendUserLegacyEmail] Error {$e->getMessage()}");

            return;
        }

        try {
            $template = $this->getEmailTemplateOption($ticket, $context, false);
        } catch (\InvalidArgumentException $e) {
            $context->getLogger()->warn("[SendUserLegacyEmail] Error {$e->getMessage()}");

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
            ->setToPerson($ticket->person)
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
            $context->getLogger()->info('[SendUserLegacyEmail] Identified as an automatic email');
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

        $defaultVars = array_merge($defaultVars, $emailBuilder->getCommonVars(false));

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

        $context->getLogger()->info(sprintf('[SendUserLegacyEmail] Sent message in %.3fs', microtime(true) - $startTime));
    }

    /**
     * {@inheritdoc}
     */
    public function isNoop(Ticket $ticket, ExecutorContextInterface $context)
    {
        if (!$this->getContainer()->getEmailAccountManager()->countOutgoingAccounts()) {
            $context->getLogger()->debug('[SendUserLegacyEmail] no outgoing email accounts are defined');

            return true;
        }

        if ($context->getVars()->get('mute_user_emails')) {
            $context->getLogger()->debug('[SendUserLegacyEmail] mute_user_emails = true');

            return true;
        }

        return false;
    }
}

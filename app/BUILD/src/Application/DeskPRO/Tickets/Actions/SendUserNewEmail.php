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
 * @category Tickets
 */

namespace Application\DeskPRO\Tickets\Actions;

use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Entity\TicketMessage;
use Application\DeskPRO\Tickets\ExecutorContextInterface;
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
        // Vars
        //-------------------------

        $defaultVars = $this->getStandardEmailVars($ticket, $context, 'user');

        //-------------------------
        // Send emails
        //-------------------------

        $factory = $this->getContainer()->get('email.user_viewmodel_factory');

        switch ($context->getEventType()) {
            case 'newticket':
                $viewModel = $factory->createTicketNewAutoreplyModel($ticket);
                break;
            case 'newreply':
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
                    $lastMessage = array_pop($messages);
                    $viewModel   = $factory->createTicketReplyByAgentModel($ticket, $lastMessage);
                } else {
                    $context->getLogger()->info('No reply to send: '.$context->getEventType());

                    return;
                }
                break;
            case 'update':
                $viewModel = $factory->createTicketNewAutoreplyModel($ticket);
                break;
            default:
                $context->getLogger()->info('Unknown event type: '.$context->getEventType());

                return;
        }

        $message = $this->getContainer()->get('email.email_sender')
            ->prepareMessage($viewModel, ['to' => $ticket->person]);

        $fromEmail = $fromAccount->getUseEmailAddress();
        $fromName  = $this->renderFromName($this->getActionOption('from_name'), $ticket, $context, 'user');
        $message->setFrom($fromEmail, $fromName);

        $mailer = $this->getContainer()->get('mailer');

        try {
            $mailer->send($message);
        } catch (\Exception $e) {
            $context->getLogger()->error(
                sprintf('Exception: [%s] %s', $e->getCode(), $e->getMessage()),
                ['exception' => $e]
            );

            throw $e;
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

        return false;
    }
}

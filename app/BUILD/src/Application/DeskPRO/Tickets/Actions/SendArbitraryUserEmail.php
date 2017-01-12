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

use Application\DeskPRO\EmailGateway\PersonFromEmailProcessor;
use Application\DeskPRO\EmailGateway\Reader\Item\EmailAddress;
use Application\DeskPRO\Entity\Organization;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Entity\TicketMessage;
use Application\DeskPRO\Tickets\ExecutorContextInterface;
use Orb\Util\CheckedOptionsArray;

/**
 * Send an email to the user.
 *
 * @option bool     template          The template to send
 * @option bool     from_name         Who to send the email from
 * @option bool     from_account      The account to send from (falsey for ticket account)
 * @option string[] emails            Email addresses to send to
 * @option bool     send_org_managers True to send to all org managers
 */
class SendArbitraryUserEmail extends AbstractEmailAction
{
    /**
     * {@inheritdoc}
     */
    protected function getOptionsDef()
    {
        $options = new CheckedOptionsArray();
        $options->addValidNames('template', 'from_name', 'from_account', 'emails', 'send_org_managers', 'single_email', 'headers');

        return $options;
    }

    /**
     * {@inheritdoc}
     */
    public function applyAction(Ticket $ticket, ExecutorContextInterface $context)
    {
        $context->getLogger()->debug('[SendArbitraryUserEmail] Begin');
        $startTime = microtime(true);

        try {
            $fromAccount = $this->getFromEmailAccountOption($ticket, $context);
        } catch (\InvalidArgumentException $e) {
            $context->getLogger()->warn("[SendArbitraryUserEmail] Error {$e->getMessage()}");

            return;
        }

        try {
            $template = $this->getEmailTemplateOption($ticket, $context, false);
        } catch (\InvalidArgumentException $e) {
            $context->getLogger()->warn("[SendArbitraryUserEmail] Error {$e->getMessage()}");

            return;
        }

        //-------------------------
        // Vars
        //-------------------------

        $defaultVars = $this->getStandardEmailVars($ticket, $context, 'user');

        //-------------------------
        // Sort out the users to send to
        //-------------------------

        /** @var \Application\DeskPRO\Entity\Person[] $sendPeople */
        $sendPeople = [];

        if ($this->getActionOption('send_org_managers')) {
            /** @var \Application\DeskPRO\EntityRepository\Organization $orgRepo */
            $orgRepo  = $this->getContainer()->getEm()->getRepository(Organization::class);
            $managers = $orgRepo->getManagers($ticket->getOrganization());

            foreach ($managers as $p) {
                $sendPeople[$p->getEmailAddress()] = $p;
            }
        }

        $regClosed = !$this->getContainer()->getSetting('core.reg_enabled');
        foreach ($this->getActionOption('emails') as $email) {
            $person = $this->getContainer()->getEm()->getRepository(Person::class)->findOneByEmail($email);
            if ($person) {
                $sendPeople[$email] = $person;
            } else {
                if ($regClosed) {
                    continue;
                }
                $personProcessor = new PersonFromEmailProcessor();

                $eml        = new EmailAddress();
                $eml->email = $email;
                $person     = $personProcessor->createPerson($eml);

                if ($person) {
                    $sendPeople[$email] = $person;
                }
            }
        }

        if (!$sendPeople) {
            $context->getLogger()->debug('[SendArbitraryUserEmail] no people to send to');
        }

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

        $mailer = $this->getContainer()->get('mailer');

        foreach ($sendPeople as $email => $person) {
            $context->getLogger()->debug(
                sprintf(
                    '[SendArbitraryUserEmail] Sending to Person#%d %s <%s>',
                    $person->id,
                    $person->getDisplayName(),
                    $person->primary_email ? $person->primary_email->email : '?'
                )
            );

            $message = $this->getContainer()->get('email.email_sender')
                ->prepareMessage($viewModel, ['to' => $person]);

            try {
                $mailer->send($message);
            } catch (\Exception $e) {
                $context->getLogger()->error(
                    sprintf('Exception: [%s] %s', $e->getCode(), $e->getMessage()),
                    ['exception' => $e]
                );

                throw $e;
            }

            $context->getLogger()->info(sprintf('[SendArbitraryUserEmail] Sent message in %.3fs', microtime(true) - $startTime));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function isNoop(Ticket $ticket, ExecutorContextInterface $context)
    {
        if (!$this->getContainer()->getEmailAccountManager()->countOutgoingAccounts()) {
            $context->getLogger()->debug('[SendArbitraryUserEmail] no outgoing email accounts are defined');

            return true;
        }

        if ($context->getVars()->get('mute_user_emails')) {
            $context->getLogger()->debug('[SendArbitraryUserEmail] mute_user_emails = true');

            return true;
        }

        return false;
    }
}

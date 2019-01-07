<?php

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
use Application\DeskPRO\Entity\TicketTrigger;
use Application\DeskPRO\Tickets\ExecutorContextInterface;
use Application\DeskPRO\Tickets\TicketEmail;
use Application\DeskPRO\Tickets\TicketEmailBuilder;
use Application\EmailBundle\SwiftMailer\Transport\StorageTransportInterface;
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
class SendArbitraryUserNewEmail extends AbstractEmailAction
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
        $context->getLogger()->debug('[SendArbitraryUserNewEmail] Begin');
        $startTime = microtime(true);

        try {
            $fromAccount = $this->getFromEmailAccountOption($ticket, $context);
        } catch (\InvalidArgumentException $e) {
            $context->getLogger()->warn("[SendArbitraryUserNewEmail] Error {$e->getMessage()}");

            return;
        }

        try {
            $template = $this->getEmailTemplateOption($ticket, $context, false);
        } catch (\InvalidArgumentException $e) {
            $context->getLogger()->warn("[SendArbitraryUserNewEmail] Error {$e->getMessage()}");

            return;
        }

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

        $regClosed = !$this->getContainer()->get('dp_authentication_manager.user')->isRegistrationFormVisible();
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
            $context->getLogger()->debug('[SendArbitraryUserNewEmail] no people to send to');
        }

        //-------------------------
        // Send emails
        //-------------------------

        $factory = $this->getContainer()->get('email.user_viewmodel_factory');

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
        $viewModel = $this->createViewModelFromTemplate($template, $arguments, $context);

        if (!$viewModel) {
            return;
        }

        $mailer = $this->getContainer()->get('mailer');

        $builder = TicketEmailBuilder::createFromContainer($this->getContainer())
            ->setTicket($ticket)
            ->setUserMode()
            ->setTemplateName($template)
            ->setFromName($this->renderFromName($this->getActionOption('from_name'), $ticket, $context, 'user'))
            ->setMaxAttachSize(0)
            ->setLogger($context->getLogger())
            ->setHeaders($this->processHeaders($this->getActionOption('headers', []), $ticket, $context))
            ->setFromEmailAccount($fromAccount);

        foreach ($sendPeople as $email => $person) {
            $context->getLogger()->debug(
                sprintf(
                    '[SendArbitraryUserNewEmail] Sending to Person#%d %s <%s>',
                    $person->id,
                    $person->getDisplayName(),
                    $person->primary_email ? $person->primary_email->email : '?'
                )
            );

            $messagesArgs['to'] = $person;

            /** @var TicketEmail $ticketEmail */
            $ticketEmail = $builder->setToPerson($person)->buildTicketEmail();

            $message = $ticketEmail->prepareMailerMessage([], false);

            $message = $this->getContainer()->get('email.email_sender')
                ->prepareMessage($viewModel, $messagesArgs, $message);

            try {
                if ($mailer instanceof StorageTransportInterface) {
                    $id = $mailer->queueMessage($message);
                } else {
                    $mailer->send($message);
                    $id = null;
                }
                $ticketEmail->setSendmailSourceId($id);
                $this->recordEmailTicketLog($ticketEmail, $ticket, $context);
            } catch (\Exception $e) {
                $context->getLogger()->error(
                    sprintf('Exception: [%s] %s', $e->getCode(), $e->getMessage()),
                    ['exception' => $e]
                );

                throw $e;
            }

            $context->getLogger()->info(sprintf('[SendArbitraryUserNewEmail] Sent message in %.3fs', microtime(true) - $startTime));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function isNoop(Ticket $ticket, ExecutorContextInterface $context)
    {
        if (!$this->getContainer()->getEmailAccountManager()->countOutgoingAccounts()) {
            $context->getLogger()->debug('[SendArbitraryUserNewEmail] no outgoing email accounts are defined');

            return true;
        }

        if ($context->getVars()->get('mute_user_emails')) {
            $context->getLogger()->debug('[SendArbitraryUserNewEmail] mute_user_emails = true');

            return true;
        }

        return false;
    }
}

<?php

namespace Application\DeskPRO\Tickets\Actions;

use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Tickets\ExecutorContextInterface;
use Application\DeskPRO\Tickets\TicketEmailBuilder;
use DeskPRO\Bundle\AppBundle\Approval\ExecutorContext;
use DeskPRO\Bundle\AppBundle\Entity\Approval\AbstractBaseApproval;
use DeskPRO\Bundle\AppBundle\Entity\Approval\TicketApproval;
use DeskPRO\Bundle\SendmailBundle\Factory\AgentViewModelFactory;
use DeskPRO\Bundle\SendmailBundle\Sender\EmailSender;
use Orb\Util\CheckedOptionsArray;

/**
 * Class SendTicketApprovalEmail
 *
 * @option bool send_to_owner      Send approval email to owner
 * @option bool send_to_approvers  Send approval email to approvers
 * @option bool from_name          Who to send the email from
 * @option bool from_account       The account to send from (falsey for ticket account)
 * @option bool headers            Array of email headers
 */
class SendTicketApprovalEmail extends AbstractEmailAction implements ActionInterface, NoopableInterface
{
    /**
     * Approval email template
     */
    const EMAIL_TEMPLATE = 'DeskPRO:emails_common:ticket-approval-%s.html.twig';

    /**
     * {@inheritdoc}
     */
    protected function getOptionsDef()
    {
        $options = new CheckedOptionsArray();
        $options->addValidNames(
            'send_to_owner',
            'send_to_approvers',
            'from_name',
            'from_account',
            'headers'
        );

        return $options;
    }

    /**
     * {@inheritDoc}
     * @param ExecutorContext $context
     * @throws \Exception
     */
    public function applyAction(Ticket $ticket, ExecutorContextInterface $context)
    {
        $context->getLogger()->debug('[SendTicketApprovalEmail] Begin');

        $sendToOwner = $this->getActionOption('send_to_owner', false);
        $sendToApprovers = $this->getActionOption('send_to_approvers', false);

        if (!$sendToOwner && !$sendToApprovers) {
            $context->getLogger()->debug('[SendTicketApprovalEmail] Not sending to owner or approvers');

            return;
        }

        $approval = $context->getApproval();
        $approvalResponse = $context->getApprovalResponse();

        $recipients = $this->buildRecipientsList(
            $ticket,
            $approval,
            $sendToApprovers,
            $sendToOwner
        );

        if (!count($recipients)) {
            $context->getLogger()->debug('[SendTicketApprovalEmail] No recipients to send to');

            return;
        }

        $agentEmailBuilder = $this
            ->getTicketEmailBuilder($ticket, $context)
            ->setAgentMode()
        ;

        $userEmailBuilder = $this
            ->getTicketEmailBuilder($ticket, $context)
            ->setUserMode()
        ;

        if (!$agentEmailBuilder || !$userEmailBuilder) {
            return;
        }

        $vars = [
            'ticket' => $ticket,
            'approval' => $approval,
            'approval_response' => $approvalResponse,
        ];

        foreach ($recipients as $recipient) {
            $vars['recipient'] = $recipient;
            $vars['is_owner'] = $recipient->isEqualTo($ticket->getPerson());
            try {
                if ($recipient->isAgent()) {
                    if ($this->hasEmailTemplatesFeature()) {
                        $viewModel = $this->getAgentViewModelFactory()->createTicketApprovalModelByApprovalEvent(
                            $context->getEventType(),
                            $vars['ticket'],
                            $vars['approval'],
                            $vars['recipient'],
                            $vars['is_owner'],
                            $vars['approval_response']
                        );
                        $this->getEmailSender()->send($viewModel, ['to' => $recipient]);
                    } else {
                        $agentTicketEmail = $agentEmailBuilder->setToPerson($recipient)->buildTicketEmail();
                        $agentTicketEmail->send($vars);
                        $this->recordEmailTicketLog($agentTicketEmail, $ticket, $context);
                    }
                } elseif ($recipient->isUser()) {
                    if ($this->hasEmailTemplatesFeature()) {
                        $viewModel = $this->getUserViewModelFactory()->createTicketApprovalModelByApprovalEvent(
                            $context->getEventType(),
                            $vars['ticket'],
                            $vars['approval'],
                            $vars['recipient'],
                            $vars['is_owner'],
                            $vars['approval_response']
                        );
                        $this->getEmailSender()->send($viewModel, ['to' => $recipient]);
                    } else {
                        $userTicketEmail = $userEmailBuilder->setToPerson($recipient)->buildTicketEmail();
                        $userTicketEmail->send($vars);
                        $this->recordEmailTicketLog($userTicketEmail, $ticket, $context);
                    }
                }
            } catch (\Exception $e) {
                $context->getLogger()->error(
                    sprintf('[SendTicketApprovalEmail] Exception: [%s] %s', $e->getCode(), $e->getMessage()),
                    ['exception' => $e]
                );

                throw $e;
            }
        }
    }

    /**
     * @param Ticket $ticket
     * @param ExecutorContextInterface $context
     * @return TicketEmailBuilder|bool
     * @throws \Exception
     */
    private function getTicketEmailBuilder(Ticket $ticket, ExecutorContextInterface $context)
    {
        try {
            $templateName = $this->buildEmailTemplateName($context);
            $fromAccount = $this->getFromEmailAccountOption($ticket, $context);
        } catch (\InvalidArgumentException $e) {
            $context->getLogger()->warn("[SendTicketApprovalEmail] Error {$e->getMessage()}");

            return false;
        }

        return TicketEmailBuilder::createFromContainer($this->getContainer())
            ->setTicket($ticket)
            ->setTemplateName($templateName)
            ->setFromName($this->renderFromName($this->getActionOption('from_name'), $ticket, $context, 'user'))
            ->setMaxAttachSize($this->getContainer()->getSetting('core.sendemail_attach_maxsize'))
            ->setLogger($context->getLogger())
            ->setHeaders($this->processHeaders($this->getActionOption('headers', []), $ticket, $context))
            ->setFromEmailAccount($fromAccount)
        ;
    }

    /**
     * {@inheritDoc}
     */
    public function isNoop(Ticket $ticket, ExecutorContextInterface $context)
    {
        if (!($context instanceof ExecutorContext)) {
            return true;
        }

        if (!$context->getApproval()) {
            return true;
        }

        if (!($context->getApproval() instanceof TicketApproval)) {
            return true;
        }

        return false;
    }

    /**
     * @param ExecutorContextInterface $context
     * @return string
     * @throws \Exception
     */
    private function buildEmailTemplateName(ExecutorContextInterface $context)
    {
        $templateName = sprintf(
            self::EMAIL_TEMPLATE,
            str_replace('_', '-', $context->getEventType())
        );

        if (!$this->getContainer()->get('templating.email')->exists($templateName)) {
            $context->getLogger()->warn(
                sprintf('[SendTicketApprovalEmail] Template [%s] does not exist', $templateName)
            );
            throw new \InvalidArgumentException('invalid_template');
        }

        return $templateName;
    }

    /**
     * @param Ticket $ticket
     * @param AbstractBaseApproval $approval
     * @param bool $sendToApprovers
     * @param bool $sendToOwner
     * @return Person[]
     * @throws \Exception
     */
    private function buildRecipientsList(
        Ticket $ticket,
        AbstractBaseApproval $approval,
        $sendToApprovers,
        $sendToOwner
    ) {
        $recipients = [];

        $reducer = function (array $recipients, Person $person) {
            return array_merge($recipients, [$person->getId() => $person]);
        };

        if ($sendToApprovers) {
            /** @var Person[] $recipients */
            $approvers = $this->getContainer()->get('doctrine')
                ->getRepository(AbstractBaseApproval::class)
                ->getApproversFromApproval($approval)
            ;
            $recipients = array_reduce($approvers, $reducer, $recipients);
        }

        if ($sendToOwner) {
            $recipients = array_reduce([$ticket->getPerson()], $reducer, $recipients);
        }

        return array_values($recipients);
    }

    /**
     * @return bool
     * @throws \Exception
     */
    private function hasEmailTemplatesFeature()
    {
        static $hasFeature = null;

        return is_bool($hasFeature) ? $hasFeature : $hasFeature = $this
            ->getContainer()
            ->get('deskpro.feature_flags')
            ->hasBeta('email_templates')
        ;
    }

    /**
     * @return AgentViewModelFactory
     * @throws \Exception
     */
    private function getAgentViewModelFactory()
    {
        return $this->getContainer()->get('email.agent_viewmodel_factory');
    }

    /**
     * @return AgentViewModelFactory
     * @throws \Exception
     */
    private function getUserViewModelFactory()
    {
        return $this->getContainer()->get('email.user_viewmodel_factory');
    }

    /**
     * @return EmailSender
     * @throws \Exception
     */
    private function getEmailSender()
    {
        return $this->getContainer()->get('email.email_sender');
    }
}

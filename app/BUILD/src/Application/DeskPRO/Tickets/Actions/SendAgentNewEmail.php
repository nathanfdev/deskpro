<?php

namespace Application\DeskPRO\Tickets\Actions;

use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Entity\TicketFilterSubscription;
use Application\DeskPRO\Entity\TicketMessage;
use Application\DeskPRO\Entity\TicketTrigger;
use Application\DeskPRO\ORM\StateChange\ChangeCollection;
use Application\DeskPRO\Tickets\ExecutorContextInterface;
use Application\DeskPRO\Tickets\Notifications\AgentNotifyListBuilder;
use Application\DeskPRO\Tickets\TicketEmail;
use Application\DeskPRO\Tickets\TicketEmailBuilder;
use Application\DeskPRO\Tickets\Util as TicketUtil;
use DeskPRO\Bundle\SendmailBundle\View\Model\AgentTicketUpdate;
use Orb\Util\CheckedOptionsArray;

/**
 * Send an email to one or more agents.
 *
 * @option bool template       The template to send
 * @option bool agent_ids      Agents to send to
 * @option bool from_name      Who to send the email from
 * @option bool from_account   The account to send from (falsey for ticket account)
 */
class SendAgentNewEmail extends AbstractEmailAction implements ActionInterface, NoopableInterface
{
    /**
     * {@inheritdoc}
     */
    protected function getOptionsDef()
    {
        $options = new CheckedOptionsArray();
        $options->addRequiredNames('agent_ids');
        $options->addValidNames('template', 'from_name', 'from_account', 'headers');

        return $options;
    }

    /**
     * @param Ticket                   $ticket
     * @param array                    $agentIds
     * @param ExecutorContextInterface $context
     *
     * @return array
     */
    private function resolveAgents(Ticket $ticket, array $agentIds, ExecutorContextInterface $context)
    {
        $agents = [];

        $personContext   = $context->getPersonContext();
        $isNotifDisabled = $this->getContainer()->getSetting('agent.disable_notifications');
        $changeDetector  = $this->getContainer()->getTicketFilterChangeDetector();

        foreach ($agentIds as $aid) {
            if ('all_agents' === $aid) {
                $context->getLogger()->debug('[SendAgentNewEmail] notify_list all agents');
                $agents = $this->getContainer()->getAgentData()->getAgents();
                break;
            }

            if ($aid == 'notify_list') {
                $context->getLogger()->debug('[SendAgentNewEmail] notify_list using notify_list');
                if ($isNotifDisabled) {
                    continue;
                }

                /** @var \Application\DeskPRO\EntityRepository\TicketFilterSubscription $subscriptionRepo */
                $subscriptionRepo = $this->getContainer()->getEm()->getRepository(TicketFilterSubscription::class);
                $forAgentIds      = $subscriptionRepo->getSubscribedActiveAgentIds();

                $changeSet   = $changeDetector->getFilterChangeSet($ticket, $context, $forAgentIds);
                $listBuilder = new AgentNotifyListBuilder($ticket, $changeSet, $subscriptionRepo);
                $listBuilder->setLogger($context->getLogger());

                $notify = $listBuilder->genNotifyList();

                foreach ($notify as $n) {
                    // dont send to self
                    if ($personContext && $personContext === $n['agent']) {
                        $override = false;
                        if ($personContext->getPref('agent_notify_override.all.email')) {
                            $override = true;
                        } elseif ($personContext->getPref('agent_notify_override.forward.email') && $context->getEventType() == 'newticket' && $context->getEventMethod() == 'email') {
                            $override = true;
                        }

                        if (!$override) {
                            $context->getLogger()->debug('[SendAgentNewEmail] notify_list skipping self');
                            continue;
                        } else {
                            $context->getLogger()->debug('[SendAgentNewEmail] notify_list sending to self because got override preference');
                        }
                    }
                    if (in_array('email', $n['types'])) {
                        $agents[] = $n['agent'];
                    }
                }

                $forceList = $context->getVars()->get('agent_force_subscription_list', []);
                if ($forceList) {
                    $context->getLogger()->debug('[SendAgentNewEmail] Appending force list');
                    $agents = array_merge($agents, $forceList);
                }
            } else {
                $agentData = $this->getContainer()->getAgentData();
                $agents    = array_merge($agents, $agentData->selectAgents($aid, $personContext, $ticket));
            }
        }

        if ($context->getVars()->has('mention_agents')) {
            $aids = array_map(function ($a) {
                return $a->getId();
            }, $context->getVars()->get('mention_agents'));
            $context->getLogger()->debug('[SendAgentNewEmail] notify_list adding mentioned agents: '.implode(', ', $aids));
            $agents = array_merge($agents, array_values($context->getVars()->get('mention_agents')));
        }

        if (!$agents) {
            $context->getLogger()->debug('[SendAgentNewEmail] notify_list is empty');

            return [];
        }

        $setAgents = [];
        foreach ($agents as $a) {
            if (!isset($setAgents[$a->getId()]) && $a->isAgent() && !$a->isDeleted() && !$a->isDisabled()) {
                $a->loadHelper('Agent');
                $setAgents[$a->getId()] = $a;
            }
        }

        $aids = array_map(function ($a) {
            return $a->getId();
        }, $setAgents);
        $context->getLogger()->debug('[SendAgentNewEmail] notify_list final list: '.implode(', ', $aids));

        return array_values($setAgents);
    }

    /**
     * {@inheritdoc}
     */
    public function applyAction(Ticket $ticket, ExecutorContextInterface $context)
    {
        $context->getLogger()->debug('[SendAgentNewEmail] Begin :: agent_ids = '.implode(', ', $this->getActionOption('agent_ids')));
        $startTime = microtime(true);

        $agents = $this->resolveAgents($ticket, $this->getActionOption('agent_ids'), $context);

        if (!$agents) {
            $context->getLogger()->debug('[SendAgentNewEmail] No agents to send to');

            return;
        }

        try {
            $fromAccount = $this->getFromEmailAccountOption($ticket, $context);
        } catch (\InvalidArgumentException $e) {
            $context->getLogger()->warn("[SendAgentNewEmail] Error {$e->getMessage()}");

            return;
        }

        try {
            $template = $this->getEmailTemplateOption($ticket, $context, false);
        } catch (\InvalidArgumentException $e) {
            $context->getLogger()->warn("[SendAgentNewEmail] Error {$e->getMessage()}");

            return;
        }

        //-------------------------
        // Send emails
        //-------------------------

        $sentCount = 0;

        $factory = $this->getContainer()->get('email.agent_viewmodel_factory');

        $messagesArgs = [
            'template' => $template,
        ];

        $state          = $ticket->getStateChangeRecorder();
        $fnCheckNewPart = function ($agent) use ($state, $ticket) {
            $has = false;
            foreach ($ticket->getParticipants() as $p) {
                if ($p->getPerson() === $agent) {
                    $has = true;
                    break;
                }
            }
            if (!$has) {
                return false;
            }

            foreach ($state->getChangesForField('participants') as $change) {
                if ($change instanceof ChangeCollection) {
                    foreach ($change->getAddedElements() as $p) {
                        if ($p->getPerson() === $agent) {
                            return true;
                        }
                    }
                }
            }

            return false;
        };

        $changedAgent        = $state->hasChangedField('agent');
        $changedAgentTeam    = $state->hasChangedField('agent_team');
        $changedParticipants = $state->hasChangedField('participants');
        $changedStatus       = $state->hasChangedField('status');

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

        $emailBuilder = TicketEmailBuilder::createFromContainer($this->getContainer())
            ->setTicket($ticket)
            ->setFromName($this->renderFromName($this->getActionOption('from_name'), $ticket, $context, 'agent'))
            ->setFromEmailAccount($fromAccount)
            ->setAgentMode()
            ->setTemplateName($template)
            ->setMaxAttachSize($this->getContainer()->getSetting('core.sendemail_attach_maxsize'))
            ->setLogger($context->getLogger())
            ->setHeaders($this->processHeaders($this->getActionOption('headers', []), $ticket, $context))
        ;

        $brandStack = $this->getContainer()->getBrandStack();
        if ($ticket->getBrand()) {
            $brandStack->push($ticket->getBrand());
        }

        /** @var Person[] $agents */
        foreach ($agents as $agent) {
            ++$sentCount;
            if ($viewModel instanceof AgentTicketUpdate) {
                $tac = TicketUtil::getTacForPerson($ticket, $agent);
                $viewModel->setTac($tac);
                $typeFlag = null;
                if ($changedAgent && $ticket->getAgent() && $ticket->getAgent() === $agent) {
                    $typeFlag = 'assigned';
                } elseif ($changedAgentTeam && $ticket->getAgentTeam() && $agent->getHelper('Agent')->isTeamMember($ticket->getAgentTeam()->getId())) {
                    $typeFlag = 'assigned_team';
                } elseif ($changedParticipants && $fnCheckNewPart($agent)) {
                    $typeFlag = 'added_part';
                } elseif ($changedStatus) {
                    $typeFlag = 'status_changed';
                }
                $viewModel->setTypeFlag($typeFlag);
            }

            $context->getLogger()->debug(
                sprintf('[SendAgentNewEmail] Sending to <Person:%d> %s', $agent->getId(), $agent->getDisplayName())
            );

            $messagesArgs['to'] = $agent;

            /** @var TicketEmail $ticketEmail */
            $ticketEmail = $emailBuilder->setToPerson($agent)->buildTicketEmail();

            $message = $ticketEmail->prepareMailerMessage([], false);
            $message = $this->getContainer()->get('email.email_sender')
                ->prepareMessage($viewModel, $messagesArgs, $message);

            try {
                $mailer->send($message);
            } catch (\Exception $e) {
                $context->getLogger()->error(
                    sprintf('[SendAgentNewEmail] Exception: [%s] %s', $e->getCode(), $e->getMessage()),
                    ['exception' => $e]
                );

                throw $e;
            }
        }

        /* If we added a brand in the stack we remove it */
        if ($ticket->getBrand()) {
            $brandStack->pop();
        }

        $context->getLogger()->info(sprintf('[SendAgentNewEmail] Send %d messages in %.3fs', $sentCount, microtime(true) - $startTime));
    }

    /**
     * {@inheritdoc}
     */
    public function isNoop(Ticket $ticket, ExecutorContextInterface $context)
    {
        if (!$this->getContainer()->getEmailAccountManager()->countOutgoingAccounts()) {
            $context->getLogger()->debug('[SendAgentNewEmail] no outgoing email accounts are defined');

            return true;
        }

        if ($context->getVars()->get('mute_agent_emails')) {
            $context->getLogger()->debug('[SendAgentNewEmail] mute_agent_emails = true');

            return true;
        }

        return false;
    }
}

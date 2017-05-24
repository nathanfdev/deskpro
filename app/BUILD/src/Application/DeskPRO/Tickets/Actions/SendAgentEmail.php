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

use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Entity\TicketFilterSubscription;
use Application\DeskPRO\ORM\StateChange\ChangeCollection;
use Application\DeskPRO\Tickets\ExecutorContextInterface;
use Application\DeskPRO\Tickets\Notifications\AgentNotifyListBuilder;
use Application\DeskPRO\Tickets\TicketEmailBuilder;
use Orb\Util\CheckedOptionsArray;

/**
 * Send an email to one or more agents.
 *
 * @option bool template       The template to send
 * @option bool agent_ids      Agents to send to
 * @option bool from_name      Who to send the email from
 * @option bool from_account   The account to send from (falsey for ticket account)
 */
class SendAgentEmail extends AbstractEmailAction implements ActionInterface, NoopableInterface
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
                $context->getLogger()->debug('[SendAgentEmail] notify_list all agents');
                $agents = $this->getContainer()->getAgentData()->getAgents();
                break;
            }

            if ($aid == 'notify_list') {
                $context->getLogger()->debug('[SendAgentEmail] notify_list using notify_list');
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
                            $context->getLogger()->debug('[SendAgentEmail] notify_list skipping self');
                            continue;
                        } else {
                            $context->getLogger()->debug('[SendAgentEmail] notify_list sending to self because got override preference');
                        }
                    }
                    if (in_array('email', $n['types'])) {
                        $agents[] = $n['agent'];
                    }
                }

                $forceList = $context->getVars()->get('agent_force_subscription_list', []);
                if ($forceList) {
                    $context->getLogger()->debug('[SendAgentEmail] Appending force list');
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
            $context->getLogger()->debug('[SendAgentEmail] notify_list adding mentioned agents: '.implode(', ', $aids));
            $agents = array_merge($agents, array_values($context->getVars()->get('mention_agents')));
        }

        if (!$agents) {
            $context->getLogger()->debug('[SendAgentEmail] notify_list is empty');

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
        }, $set_agents);
        $context->getLogger()->debug('[SendAgentEmail] notify_list final list: '.implode(', ', $aids));

        return array_values($setAgents);
    }

    /**
     * {@inheritdoc}
     */
    public function applyAction(Ticket $ticket, ExecutorContextInterface $context)
    {
        $context->getLogger()->debug('[SendAgentEmail] Begin :: agent_ids = '.implode(', ', $this->getActionOption('agent_ids')));
        $startTime = microtime(true);

        $agents = $this->resolveAgents($ticket, $this->getActionOption('agent_ids'), $context);

        if (!$agents) {
            $context->getLogger()->debug('[SendAgentEmail] No agents to send to');

            return;
        }

        try {
            $fromAccount = $this->getFromEmailAccountOption($ticket, $context);
        } catch (\InvalidArgumentException $e) {
            $context->getLogger()->warn("[SendAgentEmail] Error {$e->getMessage()}");

            return;
        }

        try {
            $template = $this->getEmailTemplateOption($ticket, $context, false);
        } catch (\InvalidArgumentException $e) {
            $context->getLogger()->warn("[SendAgentEmail] Error {$e->getMessage()}");

            return;
        }

        //-------------------------
        // Vars
        //-------------------------

        $defaultVars = $this->getStandardEmailVars($ticket, $context, 'agent');

        //-------------------------
        // Send emails
        //-------------------------

        $sentCount = 0;

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

        if ($context->getVars()->has('mention_agents')) {
            $mentionedAgentsMap = array_fill_keys(array_keys($context->getVars()->get('mention_agents')), true);
        } else {
            $mentionedAgentsMap = [];
        }

        $emailBuilder = TicketEmailBuilder::createFromContainer($this->getContainer());
        $emailBuilder
            ->setTicket($ticket)
            ->setFromName($this->renderFromName($this->getActionOption('from_name'), $ticket, $context, 'agent'))
            ->setFromEmailAccount($fromAccount)
            ->setAgentMode()
            ->setTemplateName($template)
            ->setMaxAttachSize($this->getContainer()->getSetting('core.sendemail_attach_maxsize'))
            ->setLogger($context->getLogger())
            ->setHeaders($this->processHeaders($this->getActionOption('headers', []), $ticket, $context))
        ;

        $defaultVars = array_merge($defaultVars, $emailBuilder->getCommonVars(true));

        $changedAgent        = $state->hasChangedField('agent');
        $changedAgentTeam    = $state->hasChangedField('agent_team');
        $changedParticipants = $state->hasChangedField('participants');
        $changedStatus       = $state->hasChangedField('status');

        if ($state->hasChangedField('ticket_sla_status')) {
            $change = $state->getLastChangeForField('ticket_sla_status');
            $new    = $change->getNew();

            $defaultVars['sla']        = $new['sla'];
            $defaultVars['sla_status'] = $new['status'];
        }

        /** @var Person[] $agents */
        foreach ($agents as $agent) {
            ++$sentCount;

            $context->getLogger()->debug(sprintf('[SendAgentEmail] Sending to <Person:%d> %s', $agent->getId(), $agent->getDisplayName()));
            $vars = $defaultVars;

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

            $vars['type_flag'] = $typeFlag;
            if (isset($mentionedAgentsMap[$agent->getId()])) {
                $vars['is_my_mention'] = true;
            }

            $vars['is_mention_email'] = $context->getVars()->has('mention_agents') ? true : false;

            try {
                $ticketEmail = $emailBuilder->setToPerson($agent)->buildTicketEmail();
                $ticketEmail->send($vars);
                $this->recordEmailTicketLog($ticketEmail, $ticket, $context);
            } catch (\Exception $e) {
                $context->getLogger()->error(
                    sprintf('[SendAgentEmail] Exception: [%s] %s', $e->getCode(), $e->getMessage()),
                    ['exception' => $e]
                );

                throw $e;
            }
        }

        $context->getLogger()->info(sprintf('[SendAgentEmail] Send %d messages in %.3fs', $sentCount, microtime(true) - $startTime));
    }

    /**
     * {@inheritdoc}
     */
    public function isNoop(Ticket $ticket, ExecutorContextInterface $context)
    {
        if (!$this->getContainer()->getEmailAccountManager()->countOutgoingAccounts()) {
            $context->getLogger()->debug('[SendAgentEmail] no outgoing email accounts are defined');

            return true;
        }

        if ($context->getVars()->get('mute_agent_emails')) {
            $context->getLogger()->debug('[SendAgentEmail] mute_agent_emails = true');

            return true;
        }

        return false;
    }
}

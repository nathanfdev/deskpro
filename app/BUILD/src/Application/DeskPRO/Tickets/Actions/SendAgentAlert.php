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

use Application\DeskPRO\DBAL\Connection;
use Application\DeskPRO\Entity\AgentAlert;
use Application\DeskPRO\Entity\Language;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Entity\TicketFilterSubscription;
use Application\DeskPRO\Tickets\ExecutorContextInterface;
use Application\DeskPRO\Tickets\Notifications\AgentNotifyListBuilder;
use DeskPRO\Bundle\AppBundle\Notification\Event\LegacySystemEvent;
use Orb\Util\CheckedOptionsArray;

/**
 * Send browser alerts. Note that this action isn't meant to be used with a trigger,
 * so it's called manually as part of TicketManager.
 *
 * The reason is that it needs info about the ticket logs so alerts can be properly dismissed
 * in the interface.
 *
 * @option array agent_ids      Agents to send to
 * @option TicketLog[]          ticket_logs
 */
class SendAgentAlert extends AbstractContainerAwareAction implements ActionInterface
{
    /**
     * {@inheritdoc}
     */
    protected function getOptionsDef()
    {
        $options = new CheckedOptionsArray();
        $options->addRequiredNames('agent_ids', 'ticket_logs');

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

        foreach ($agentIds as $aid) {
            // -1 = current user
            if ($aid == -1) {
                if ($context->getPersonContext() && $context->getPersonContext()->isAgent()) {
                    $agents[] = $context->getPersonContext();
                }

            // assigned agent
            } elseif ($aid == 'agent') {
                if ($ticket->getAgent()) {
                    $agents[] = $ticket->getAgent();
                }

            // agents of assigned team
            } elseif ($aid == 'team') {
                if ($ticket->getAgentTeam()) {
                    foreach ($ticket->getAgentTeam()->getPersonList() as $agent) {
                        $agents[] = $agent;
                    }
                }

            // followers
            } elseif ($aid == 'followers') {
                if ($agentFollowers = $ticket->getAgentParticipants()) {
                    foreach ($agentFollowers as $agent) {
                        $agents[] = $agent;
                    }
                }

            // based on notify list
            } elseif ($aid == 'notify_list') {
                /** @var \Application\DeskPRO\EntityRepository\TicketFilterSubscription $subscriptionRepo */
                $subscriptionRepo = $this->getContainer()->getEm()->getRepository(TicketFilterSubscription::class);
                $forAgentIds      = $subscriptionRepo->getSubscribedActiveAgentIds();

                $changeDetect = $this->getContainer()->getTicketFilterChangeDetector();
                $changeSet    = $changeDetect->getFilterChangeSet($ticket, $context, $forAgentIds);
                $listBuilder  = new AgentNotifyListBuilder($ticket, $changeSet, $subscriptionRepo);

                $listBuilder->setLogger($context->getLogger());

                $notify = $listBuilder->genNotifyList();

                $personContext = $context->getPersonContext();
                foreach ($notify as $n) {
                    // dont send to self
                    if ($personContext && $personContext === $n['agent']) {
                        $override = false;
                        if ($personContext->getPref('agent_notify_override.all.alert')) {
                            $override = true;
                        } elseif ($personContext->getPref('agent_notify_override.forward.alert') && $context->getEventType() == 'newticket' && $context->getEventMethod() == 'email') {
                            $override = true;
                        }

                        if (!$override) {
                            $context->getLogger()->debug('[SendAgentAlert] notify_list skipping self');
                            continue;
                        } else {
                            $context->getLogger()->debug('[SendAgentAlert] notify_list sending to self because got override preference');
                        }
                    }
                    if (in_array('alert', $n['types'])) {
                        $agents[] = $n['agent'];
                    }
                }

            // specific agents
            } else {
                if ($agent = $this->getContainer()->getAgentData()->get($aid)) {
                    $agents[] = $agent;
                }
            }
        }

        if (!$agents) {
            return [];
        }

        $agents = array_unique($agents);

        return $agents;
    }

    /**
     * {@inheritdoc}
     */
    public function applyAction(Ticket $ticket, ExecutorContextInterface $context)
    {
        $context->getLogger()->debug('[SendAgentAlert] Begin :: agent_ids = '.implode(', ', $this->getActionOption('agent_ids')));
        $startTime = microtime(true);

        $agents = $this->resolveAgents($ticket, $this->getActionOption('agent_ids'), $context);

        if (!$agents) {
            $context->getLogger()->debug('[SendAgentAlert] No agents to send to');

            return;
        }

        $vars = [
            'is_new_ticket'      => $ticket->getStateChangeRecorder()->isNewTicket(),
            'is_new_agent_reply' => $ticket->getStateChangeRecorder()->hasNewAgentReply(),
            'is_new_agent_note'  => $ticket->getStateChangeRecorder()->hasNewAgentNote(),
            'is_new_user_reply'  => $ticket->getStateChangeRecorder()->hasNewUserReply(),
            'ticket'             => $ticket,
            'performer'          => $context->getPersonContext(),
            'log_items'          => $this->getActionOption('ticket_logs'),
        ];

        $logIds = array_map(function ($l) {
            return $l->getId();
        }, $vars['log_items']);
        $alertData = [
            '@fetch_types'       => ['ticket' => 'DeskPRO:Ticket', 'performer' => 'DeskPRO:Person', 'log_items' => 'DeskPRO:TicketLog'],
            'ticket'             => $ticket->getId(),
            'performer'          => $vars['performer'] ? $vars['performer']->getId() : 0,
            'is_new_ticket'      => $vars['is_new_ticket'],
            'is_new_agent_reply' => $vars['is_new_agent_reply'],
            'is_new_agent_note'  => $vars['is_new_agent_note'],
            'is_new_user_reply'  => $vars['is_new_user_reply'],
            'log_items'          => $logIds,
        ];

        $em  = $this->getContainer()->getEm();
        $tpl = $this->getContainer()->getTemplating();
        $tr  = $this->getContainer()->get('language_manager');

        /** @var Connection $connection */
        $connection = $em->getConnection();

        $agentsToLang = [];
        $languages    = [];

        /** @var Person $agent */
        foreach ($agents as $agent) {
            if (!$agent->PermissionsManager->TicketChecker->canView($ticket)) {
                continue;
            }

            $language   = $agent->getLanguage();
            $languageId = $language ? $language->getId() : 'default';

            $languages[$languageId]      = $language;
            $agentsToLang[$languageId][] = $agent;
        }

        if (isset($languages['default'])) {
            $languages['default'] = $em->getRepository(Language::class)->findOneBy([]);
        }

        $connection->beginTransaction();

        $alertsMap = [];
        $date      = date('Y-m-d H:i');

        foreach ($agentsToLang as $languageId => $agents) {
            $alertData['browser_rendered'] = $tr->callWithLanguage($languages[$languageId], function () use ($tpl, $vars) {
                return $tpl->render('AgentBundle:TicketSearch:notify-row.html.twig', $vars);
            });

            $serializedAlertData = serialize(array_merge($alertData, [
                '@target_maps' => [
                    AgentAlert::TARGET_BROWSER => ['browser_rendered'],
                ],
            ]));

            // batch insert alerts
            $alerts = [];
            foreach ($agents as $agent) {
                $alerts[] = [
                    'person_id'    => $agent->getId(),
                    'typename'     => 'tickets',
                    'date_created' => $date,
                    'data'         => $serializedAlertData,
                ];

                $alertsMap[] = [
                    'agent_id'         => $agent->getId(),
                    'browser_rendered' => $alertData['browser_rendered'],
                ];
            }

            $connection->batchInsert('agent_alerts', $alerts);

            $firstAlertId = (int) $connection->lastInsertId();
            foreach ($alertsMap as $num => &$alertMap) {
                $alertMap['alert_id'] = $firstAlertId + $num;
            }
        }

        // batch insert client messages
        if ($alertsMap) {
            foreach ($alertsMap as $alertRecord) {
                $this->getContainer()->get('event_dispatcher')->dispatch(
                    LegacySystemEvent::EVENT_NAME,
                    new LegacySystemEvent(
                        'agent-notify.tickets',
                        [
                            'type'     => 'tickets',
                            'alert_id' => $alertRecord['alert_id'],
                            'row'      => $alertRecord['browser_rendered'],
                            'icon'     => $this->getContainer()->get('avatar_resolver')->getAvatar($context->getPersonContext(), 48),
                            'target'   => $alertRecord['agent_id'],
                        ]
                ));
            }
        }

        $connection->commit();
        $context->getLogger()->info(sprintf('[SendAgentAlert] Sent %d alerts in %.3fs', count($alertsMap), microtime(true) - $startTime));
    }
}

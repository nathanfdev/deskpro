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
 * @category Entities
 */

namespace Application\DeskPRO\Tickets\Notifications;

use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\EntityRepository\TicketFilterSubscription as TicketFilterSubscriptionRepos;
use Application\DeskPRO\Monolog\NullLogger;
use Application\DeskPRO\People\PersonContextInterface;
use Application\DeskPRO\Tickets\Filters\FilterChangeSet;
use Monolog\Logger;

/**
 * Class AgentNotifyListBuilder.
 */
class AgentNotifyListBuilder implements PersonContextInterface
{
    /**
     * @var \Application\DeskPRO\Entity\Ticket
     */
    private $ticket;

    /**
     * @var \Application\DeskPRO\Tickets\StateChangeRecorder
     */
    private $state;

    /**
     * @var \Application\DeskPRO\Tickets\Filters\FilterChangeSet
     */
    private $filter_changes;

    /**
     * @var \Monolog\Logger
     */
    private $logger;

    /**
     * @var \Application\DeskPRO\EntityRepository\TicketFilterSubscription
     */
    private $subs_repos;

    /**
     * @var \Application\DeskPRO\Entity\Ticket
     */
    private $person_context;

    /**
     * @param Ticket                        $ticket
     * @param FilterChangeSet               $filter_changes
     * @param TicketFilterSubscriptionRepos $filter_sub_repos
     */
    public function __construct(Ticket $ticket, FilterChangeSet $filter_changes, TicketFilterSubscriptionRepos $filter_sub_repos)
    {
        $this->ticket         = $ticket;
        $this->state          = $ticket->getStateChangeRecorder();
        $this->filter_changes = $filter_changes;
        $this->subs_repos     = $filter_sub_repos;

        $this->logger = new NullLogger();
    }

    /**
     * Sets the person context. This is the person who is firing this notification event.
     * We will not notify the person of their own action.
     *
     * @param Person $person
     */
    public function setPersonContext(Person $person)
    {
        $this->person_context = $person;
    }

    /**
     * @param Logger $logger
     */
    public function setLogger(Logger $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @param string $message
     */
    private function logMessage($message)
    {
        $this->logger->info('[AgentNotifyListBuilder] '.$message);
    }

    /**
     * Generate the notify list.
     *
     * @return array
     */
    public function genNotifyList()
    {
        // Never notify about hidden tickets
        if ($this->ticket->getStatus() == 'hidden') {
            $this->logMessage('ticket hidden, no notifications to send');

            return [];
        }

        //------------------------------
        // Sort out which kind of notification we need to send
        //------------------------------

        $event_types = [
            'new'                  => false,
            'agent_reply'          => false,
            'agent_note'           => false,
            'user_reply'           => false,
            'assign_change'        => $this->state->hasChangedField('agent'),
            'assign_team_change'   => $this->state->hasChangedField('agent_team'),
            'assign_follow_change' => $this->state->hasChangedField('participants'),
            'property_change'      => false,
        ];

        if ($this->state->isNewTicket()) {
            $event_types['new'] = true;
            $this->logMessage('notify_new = true (new ticket)');
        } elseif (
            // A ticket that was just validated counts as new
            $this->ticket->getStatus() != 'hidden'
            && $this->state->hasChangedField('hidden_status')
            && $this->state->getFirstChangeForField('hidden_status')->getOld() == 'validating'
        ) {
            $event_types['new'] = true;
            $this->logMessage('notify_new = true (was validating)');
        }

        if ($this->state->hasNewAgentNote()) {
            $event_types['agent_note'] = true;
            $this->logMessage('notify_agent_note = true');
        } elseif ($this->state->hasNewAgentReply()) {
            $event_types['agent_reply'] = true;
            $this->logMessage('notify_agent_reply = true');
        } elseif ($this->state->hasNewUserReply()) {
            $event_types['user_reply'] = true;
            $this->logMessage('notify_user_reply = true');
        }

        // Its a property change subscription event if its not a reply
        if (!$event_types['new'] && !$event_types['agent_reply'] && !$event_types['agent_note'] && !$event_types['user_reply']) {
            $event_types['property_change'] = true;
        }

        //------------------------------
        // Build list
        //------------------------------

        $agent_subs  = $this->getMatchingSubscriptions();
        $notify_list = [];

        foreach ($this->filter_changes->getChangedFilters() as $filter_change) {
            $filter   = $filter_change->getFilter();
            $filterId = $filter['id'];

            $agents_with_new_match  = $filter_change->getAgentsWithNewMatch();
            $agents_with_orig_match = $filter_change->getAgentsWithOriginalMatch();

            // New ticket entering a list
            // - If its new, then we check subs for everyone
            // - Other notify types, we have to ignore 'all' for entering a list
            foreach ($agents_with_new_match as $agentId => $agent) {
                if (!isset($agent_subs[$agentId][$filterId])) {
                    continue;
                }

                $sub   = $agent_subs[$agentId][$filterId];
                $types = $this->getSubTypesForFilterNewMatch($event_types, isset($agents_with_orig_match[$agentId]), $filter, $sub);
                if ($types) {
                    $this->addTypesToList($notify_list, $agent, $filter, 'new', $types);
                }
            }

            // Notify about changes done to a ticket in a subscribed list
            // AKA a ticket changed but we want to notify subscribers in whatever filter it was in last
            foreach ($agents_with_orig_match as $agentId => $agent) {
                if (!isset($agent_subs[$agentId][$filterId])) {
                    continue;
                }

                $sub   = $agent_subs[$agentId][$filterId];
                $types = $this->getSubTypesForFilterOrigMatch($event_types, isset($agents_with_new_match[$agentId]), $filter, $sub);
                if ($types) {
                    $this->addTypesToList($notify_list, $agent, $filter, 'update', $types);
                }
            }
        }

        $this->logMessage(sprintf('%d agents with notifications', count($notify_list)));

        return $notify_list;
    }

    /**
     * @param array  $notify_list
     * @param Person $agent
     * @param array  $filter
     * @param        $change_type
     * @param array  $notify_types
     */
    private function addTypesToList(array &$notify_list, Person $agent, array $filter, $change_type, array $notify_types)
    {
        $agentId  = $agent->getId();
        $filterId = $filter['id'];

        if (!isset($notify_list[$agentId])) {
            $notify_list[$agentId] = [
                'agent'       => $agent,
                'filter_subs' => [],
                'types'       => [],
            ];
        }
        if (!isset($notify_list[$agentId]['filter_subs'][$filterId])) {
            $notify_list[$agentId]['filter_subs'][$filterId] = [
                'filter'    => $filter,
                'is_new'    => false,
                'is_update' => false,
                'types'     => [],
            ];
        }

        $notify_list[$agentId]['filter_subs'][$filterId]["is_$change_type"] = true;
        $notify_list[$agentId]['filter_subs'][$filterId]['types']           = array_merge($notify_list[$agentId]['filter_subs'][$filterId]['types'], $notify_types);
        $notify_list[$agentId]['filter_subs'][$filterId]['types']           = array_unique($notify_list[$agentId]['filter_subs'][$filterId]['types']);

        $this->logMessage(sprintf('Filter subscription match -- Agent(%s) Filter(%s) Types(%s)', $agentId, $filterId, implode(', ', $notify_list[$agentId]['filter_subs'][$filterId]['types'])));

        $notify_list[$agentId]['types'] = array_merge($notify_list[$agentId]['types'], $notify_types);
        $notify_list[$agentId]['types'] = array_unique($notify_list[$agentId]['types']);
    }

    /**
     * @param array $event_types
     * @param       $with_origmatch
     * @param array $filter
     * @param array $sub
     *
     * @return array
     */
    private function getSubTypesForFilterNewMatch(array $event_types, $with_origmatch, array $filter, array $sub)
    {
        $types    = [];
        $sys_name = $filter['sys_name'];

        if ($event_types['new']) {
            if ($sub['email_created']) {
                $types[] = 'email';
            }
            if ($sub['alert_created']) {
                $types[] = 'alert';
            }
        } elseif ($sys_name != 'all') {
            $email_new = $sub['email_new'];
            $alert_new = $sub['alert_new'];

            if (
                (!$sys_name && $email_new && !$with_origmatch)
                || (($sys_name == 'agent' || $sys_name == 'unassigned') && $event_types['assign_change'] && $email_new)
                || ($sys_name == 'agent_team' && $event_types['assign_team_change'] && $email_new)
                || ($sys_name == 'participant' && $event_types['assign_follow_change'] && $email_new)
            ) {
                $types[] = 'email';
            }

            if (
                (!$sys_name && $alert_new && $with_origmatch)
                || (($sys_name == 'agent' || $sys_name == 'unassigned') && $event_types['assign_change'] && $alert_new)
                || ($sys_name == 'agent_team' && $event_types['assign_team_change'] && $alert_new)
                || ($sys_name == 'participant' && $event_types['assign_follow_change'] && $alert_new)
            ) {
                $types[] = 'alert';
            }
        }

        return $types;
    }

    /**
     * @param array $event_types
     * @param       $with_newmatch
     * @param array $filter
     * @param array $sub
     *
     * @return array
     */
    private function getSubTypesForFilterOrigMatch(array $event_types, $with_newmatch, array $filter, array $sub)
    {
        $types = [];

        if ($event_types['property_change'] && $sub['email_property_change']) {
            $types[] = 'email';
        } elseif ($event_types['agent_note'] && $sub['email_agent_note']) {
            $types[] = 'email';
        } elseif ($event_types['agent_reply'] && $sub['email_agent_activity']) {
            $types[] = 'email';
        } elseif ($event_types['user_reply'] && $sub['email_user_activity']) {
            $types[] = 'email';
        }

        if ($event_types['property_change'] && $sub['alert_property_change']) {
            $types[] = 'alert';
        } elseif ($event_types['agent_note'] && $sub['alert_agent_note']) {
            $types[] = 'alert';
        } elseif ($event_types['agent_reply'] && $sub['alert_agent_activity']) {
            $types[] = 'alert';
        } elseif ($event_types['user_reply'] && $sub['alert_user_activity']) {
            $types[] = 'alert';
        }

        // If orig matched but its not a new match,
        // then we know it's left this list
        if (!$with_newmatch) {
            $sys_name = $filter['sys_name'];

            if (
                (!$sys_name && $sub['email_leave'])
                || (($sys_name == 'agent' || $sys_name == 'unassigned') && $event_types['assign_change'] && $sub['email_leave'])
                || ($sys_name == 'agent_team' && $event_types['assign_team_change'] && $sub['email_leave'])
                || ($sys_name == 'participant' && $event_types['assign_follow_change'] && $sub['email_leave'])
            ) {
                $types[] = 'email';
            }
            if (
                (!$sys_name && $sub['alert_leave'])
                || (($sys_name == 'agent' || $sys_name == 'unassigned') && $event_types['assign_change'] && $sub['alert_leave'])
                || ($sys_name == 'agent_team' && $event_types['assign_team_change'] && $sub['alert_leave'])
                || ($sys_name == 'participant' && $event_types['assign_follow_change'] && $sub['alert_leave'])
            ) {
                $types[] = 'alert';
            }
        }

        return $types;
    }

    /**
     * Get an array of subscriptions for the agents and filters.
     *
     * @return array
     */
    public function getMatchingSubscriptions()
    {
        $forAgentIds  = [];
        $forFilterIds = [];

        $contextPersonId = $this->person_context ? $this->person_context->getId() : null;

        foreach ($this->filter_changes->getChangedFilters() as $filterId => $changes) {
            $forFilterIds[] = $filterId;

            foreach ($changes->getAgentsWithOriginalMatch() as $agentId => $agent) {
                if ($contextPersonId != $agentId) {
                    $forAgentIds[] = $agentId;
                }
            }
            foreach ($changes->getAgentsWithNewMatch() as $agentId => $agent) {
                if ($contextPersonId != $agentId) {
                    $forAgentIds[] = $agentId;
                }
            }
        }

        $forFilterIds = array_unique($forFilterIds, \SORT_NUMERIC);
        $forAgentIds  = array_unique($forAgentIds, \SORT_NUMERIC);

        if (!$forAgentIds || !$forFilterIds) {
            $this->logMessage('no agents or filters match, no notifications to send');

            return [];
        }

        $this->logMessage(sprintf('There are %d changed filters for %d agents', count($forFilterIds), count($forAgentIds)));

        $agent_subs = $this->subs_repos->getForAgents($forAgentIds, $forFilterIds);
        $this->logMessage(sprintf("\tThere are %d matching subscriptions", count($agent_subs)));

        return $agent_subs;
    }
}

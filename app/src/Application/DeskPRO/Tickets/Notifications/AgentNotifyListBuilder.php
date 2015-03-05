<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/  |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2014, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at https://www.deskpro.com/eula/                            |
|                                                                          |
| By using this software, you acknowledge having read the license          |
| and agree to be bound thereby.                                           |
|                                                                          |
| Please note that DeskPRO is not free software. We release the full       |
| source code for our software because we trust our users to pay us for    |
| the huge investment in time and energy that has gone into both creating  |
| this software and supporting our customers. By providing the source code |
| we preserve our customers' ability to modify, audit and learn from our   |
| work. We have been developing DeskPRO since 2001, please help us make it |
| another decade.                                                          |
|                                                                          |
| Like the work you see? Think you could make it better? We are always     |
| looking for great developers to join us: http://www.deskpro.com/jobs/    |
|                                                                          |
| ~ Thanks, Everyone at Team DeskPRO                                       |
\**************************************************************************/

/**
 * DeskPRO
 *
 * @package DeskPRO
 * @category Entities
 */

namespace Application\DeskPRO\Tickets\Notifications;

use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Entity\TicketFilter;
use Application\DeskPRO\Entity\TicketFilterSubscription;
use Application\DeskPRO\EntityRepository\TicketFilterSubscription as TicketFilterSubscriptionRepos;
use Application\DeskPRO\Monolog\NullLogger;
use Application\DeskPRO\People\PersonContextInterface;
use Application\DeskPRO\Tickets\Filters\FilterChangeSet;
use Monolog\Logger;

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
        $this->ticket          = $ticket;
        $this->state           = $ticket->getStateChangeRecorder();
        $this->filter_changes  = $filter_changes;
        $this->subs_repos      = $filter_sub_repos;

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
        $this->logger->info("[AgentNotifyListBuilder] " . $message);
    }


    /**
     * Generate the notify list.
     *
     * @return array
     */
    public function genNotifyList()
    {
        // Never notify about hidden tickets
        if ($this->ticket->status == 'hidden') {
            $this->logMessage("ticket hidden, no notifications to send");

            return array();
        }


        #------------------------------
        # Sort out which kind of notification we need to send
        #------------------------------

        $event_types = array(
            'new'                  => false,
            'agent_reply'          => false,
            'agent_note'           => false,
            'user_reply'           => false,
            'assign_change'        => $this->state->hasChangedField('agent'),
            'assign_team_change'   => $this->state->hasChangedField('agent_team'),
            'assign_follow_change' => $this->state->hasChangedField('participants'),
            'property_change'      => false,
        );

        if ($this->state->isNewTicket()) {
            $event_types['new'] = true;
            $this->logMessage("notify_new = true (new ticket)");
        } elseif (
            // A ticket that was just validated counts as new
            $this->ticket->status != 'hidden'
            && $this->state->hasChangedField('hidden_status')
            && $this->state->getFirstChangeForField('hidden_status')->getOld() == 'validating'
        ) {
            $event_types['new'] = true;
            $this->logMessage("notify_new = true (was validating)");
        }

        if ($this->state->hasNewAgentNote()) {
            $event_types['agent_note'] = true;
            $this->logMessage("notify_agent_note = true");
        } elseif ($this->state->hasNewAgentReply()) {
            $event_types['agent_reply'] = true;
            $this->logMessage("notify_agent_reply = true");
        } elseif ($this->state->hasNewUserReply()) {
            $event_types['user_reply'] = true;
            $this->logMessage("notify_user_reply = true");
        }

        // Its a property change subscription event if its not a reply
        if (!$event_types['new'] && !$event_types['agent_reply'] && !$event_types['agent_note'] && !$event_types['user_reply']) {
            $event_types['property_change'] = true;
        }

        #------------------------------
        # Build list
        #------------------------------

        $agent_subs  = $this->getMatchingSubscriptions();
        $notify_list = array();

        foreach ($this->filter_changes->getChangedFilters() as $filter_change) {
            $filter = $filter_change->getFilter();

            $agents_with_new_match = array();
            foreach ($filter_change->getAgentsWithNewMatch() as $agent) {
                $agents_with_new_match[$agent->id] = true;
            }

            $agents_with_orig_match = array();
            foreach ($filter_change->getAgentsWithOriginalMatch() as $agent) {
                $agents_with_orig_match[$agent->id] = true;
            }

            // New ticket entering a list
            // - If its new, then we check subs for everyone
            // - Other notify types, we have to ignore 'all' for entering a list
            foreach ($filter_change->getAgentsWithNewMatch() as $agent) {
                if (!isset($agent_subs[$agent->id][$filter->id])) {
                    continue;
                }

                $sub = $agent_subs[$agent->id][$filter->id];
                $types = $this->getSubTypesForFilterNewMatch($event_types, isset($agents_with_orig_match[$agent->id]), $filter, $sub);
                if ($types) {
                    $this->addTypesToList($notify_list, $agent, $filter, 'new', $types);
                }
            }

            // Notify about changes done to a ticket in a subscribed list
            // AKA a ticket changed but we want to notify subscribers in whatever filter it was in last
            foreach ($filter_change->getAgentsWithOriginalMatch() as $agent) {
                if (!isset($agent_subs[$agent->id][$filter->id])) {
                    continue;
                }

                $sub = $agent_subs[$agent->id][$filter->id];
                $types = $this->getSubTypesForFilterOrigMatch($event_types, isset($agents_with_new_match[$agent->id]), $filter, $sub);
                if ($types) {
                    $this->addTypesToList($notify_list, $agent, $filter, 'update', $types);
                }
            }
        }

        $this->logMessage(sprintf("%d agents with notifications", count($notify_list)));

        return $notify_list;
    }


    /**
     * @param array        $notify_list
     * @param Person       $agent
     * @param TicketFilter $filter
     * @param $change_type
     * @param array        $notify_types
     */
    private function addTypesToList(array &$notify_list, Person $agent, TicketFilter $filter, $change_type, array $notify_types)
    {
        if (!isset($notify_list[$agent->id])) {
            $notify_list[$agent->id] = array(
                'agent'       => $agent,
                'filter_subs' => array(),
                'types'       => array()
            );
        }
        if (!isset($notify_list[$agent->id]['filter_subs'][$filter->id])) {
            $notify_list[$agent->id]['filter_subs'][$filter->id] = array(
                'filter'     => $filter,
                'is_new'     => false,
                'is_update'  => false,
                'types'      => array()
            );
        }

        $notify_list[$agent->id]['filter_subs'][$filter->id]["is_$change_type"] = true;
        $notify_list[$agent->id]['filter_subs'][$filter->id]['types'] = array_merge($notify_list[$agent->id]['filter_subs'][$filter->id]['types'], $notify_types);
        $notify_list[$agent->id]['filter_subs'][$filter->id]['types'] = array_unique($notify_list[$agent->id]['filter_subs'][$filter->id]['types']);

        $notify_list[$agent->id]['types'] = array_merge($notify_list[$agent->id]['types'], $notify_types);
        $notify_list[$agent->id]['types'] = array_unique($notify_list[$agent->id]['types']);
    }


    /**
     * @param  array                    $event_types
     * @param                           $with_origmatch
     * @param  TicketFilter             $filter
     * @param  TicketFilterSubscription $sub
     * @return array
     */
    private function getSubTypesForFilterNewMatch(array $event_types, $with_origmatch, TicketFilter $filter, TicketFilterSubscription $sub)
    {
        $types = array();
        if ($event_types['new']) {
            if ($sub->email_created) {
                $types[] = 'email';
            }
            if ($sub->alert_created) {
                $types[] = 'alert';
            }
        } elseif ($filter->sys_name != 'all') {
            if (
                (!$filter->sys_name && $sub->email_new && !$with_origmatch)
                || (($filter->sys_name == 'agent' || $filter->sys_name == 'unassigned') && $event_types['assign_change'] && $sub->email_new)
                || ($filter->sys_name == 'agent_team' && $event_types['assign_team_change'] && $sub->email_new)
                || ($filter->sys_name == 'participant' && $event_types['assign_follow_change'] && $sub->email_new)
            ) {
                $types[] = 'email';
            }
            if (
                (!$filter->sys_name && $sub->alert_new && $with_origmatch)
                || (($filter->sys_name == 'agent' || $filter->sys_name == 'unassigned') && $event_types['assign_change'] && $sub->alert_new)
                || ($filter->sys_name == 'agent_team' && $event_types['assign_team_change'] && $sub->alert_new)
                || ($filter->sys_name == 'participant' && $event_types['assign_follow_change'] && $sub->alert_new)
            ) {
                $types[] = 'alert';
            }
        }

        return $types;
    }


    /**
     * @param  array                    $event_types
     * @param $with_newmatch
     * @param  TicketFilter             $filter
     * @param  TicketFilterSubscription $sub
     * @return array
     */
    private function getSubTypesForFilterOrigMatch(array $event_types, $with_newmatch, TicketFilter $filter, TicketFilterSubscription $sub)
    {
        $types = array();

        if ($event_types['property_change'] && $sub->email_property_change) {
            $types[] = 'email';
        } elseif ($event_types['agent_note'] && $sub->email_agent_note) {
            $types[] = 'email';
        } elseif ($event_types['agent_reply'] && $sub->email_agent_activity) {
            $types[] = 'email';
        } elseif ($event_types['user_reply'] && $sub->email_user_activity) {
            $types[] = 'email';
        }

        if ($event_types['property_change'] && $sub->alert_property_change) {
            $types[] = 'alert';
        } elseif ($event_types['agent_note'] && $sub->alert_agent_note) {
            $types[] = 'alert';
        } elseif ($event_types['agent_reply'] && $sub->alert_agent_activity) {
            $types[] = 'alert';
        } elseif ($event_types['user_reply'] && $sub->alert_user_activity) {
            $types[] = 'alert';
        }

        // If orig matched but its not a new match,
        // then we know it's left this list
        if (!$with_newmatch) {
            if (
                (!$filter->sys_name && $sub->email_leave)
                || (($filter->sys_name == 'agent' || $filter->sys_name == 'unassigned') && $event_types['assign_change'] && $sub->email_leave)
                || ($filter->sys_name == 'agent_team' && $event_types['assign_team_change'] && $sub->email_leave)
                || ($filter->sys_name == 'participant' && $event_types['assign_follow_change'] && $sub->email_leave)
            ) {
                $types[] = 'email';
            }
            if (
                (!$filter->sys_name && $sub->alert_leave)
                || (($filter->sys_name == 'agent' || $filter->sys_name == 'unassigned') && $event_types['assign_change'] && $sub->alert_leave)
                || ($filter->sys_name == 'agent_team' && $event_types['assign_team_change'] && $sub->alert_leave)
                || ($filter->sys_name == 'participant' && $event_types['assign_follow_change'] && $sub->alert_leave)
            ) {
                $types[] = 'alert';
            }
        }

        return $types;
    }

    /**
     * Get an array of subscriptions for the agents and filters
     *
     * @return \Application\DeskPRO\Entity\TicketFilterSubscription[]
     */
    public function getMatchingSubscriptions()
    {
        $for_agent_ids  = array();
        $for_filter_ids = array();

        foreach ($this->filter_changes->getChangedFilters() as $filter_id => $changes) {
            $for_filter_ids[] = $filter_id;

            foreach ($changes->getAgentsWithOriginalMatch() as $agent) {
                if ($this->person_context && $this->person_context->id == $agent->id) {
                    continue;
                }
                $for_agent_ids[] = $agent->id;
            }
            foreach ($changes->getAgentsWithNewMatch() as $agent) {
                if ($this->person_context && $this->person_context->id == $agent->id) {
                    continue;
                }
                $for_agent_ids[] = $agent->id;
            }
        }

        $for_filter_ids = array_unique($for_filter_ids, \SORT_NUMERIC);
        $for_agent_ids = array_unique($for_agent_ids, \SORT_NUMERIC);

        if (!$for_agent_ids || !$for_filter_ids) {
            $this->logMessage("no agents or filters match, no notifications to send");

            return array();
        }

        $this->logMessage(sprintf("There are %d changed filters for %d agents", count($for_filter_ids), count($for_agent_ids)));

        $agent_subs = $this->subs_repos->getForAgents($for_agent_ids, $for_filter_ids);
        $this->logMessage(sprintf("\tThere are %d matching subscriptions", count($agent_subs)));

        return $agent_subs;
    }
}

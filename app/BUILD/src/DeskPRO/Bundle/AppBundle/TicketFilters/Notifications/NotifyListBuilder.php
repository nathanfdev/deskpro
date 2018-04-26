<?php

namespace DeskPRO\Bundle\AppBundle\TicketFilters\Notifications;

use DeskPRO\Bundle\AppBundle\TicketFilters\Context;
use DeskPRO\Bundle\AppBundle\TicketFilters\Diff\FilterOp;
use DeskPRO\Bundle\AppBundle\TicketFilters\Diff\TicketChange;
use DeskPRO\Bundle\AppBundle\TicketFilters\Model\Agent;
use DeskPRO\Bundle\AppBundle\TicketFilters\Model\Filter;
use DeskPRO\Bundle\AppBundle\TicketFilters\Model\Notifications\AgentFilterSubscriptionEvent;
use DeskPRO\Bundle\AppBundle\TicketFilters\Model\Notifications\AgentSubscription;
use DeskPRO\Bundle\AppBundle\TicketFilters\Model\Notifications\AgentSubscriptionSet;
use DeskPRO\Bundle\AppBundle\TicketFilters\TicketMatcher;
use DeskPRO\Component\Util\MapUtils;

class NotifyListBuilder
{
    /**
     * @var TicketMatcher
     */
    private $ticketMatcher;

    /**
     * Subscription sets.
     *
     * @var AgentSubscriptionSet[]
     */
    private $subSets;

    /**
     * @var Filter[]
     */
    private $filters;

    /**
     * @param AgentSubscriptionSet[] $subSets
     * @param TicketMatcher          $ticketMatcher
     * @param Filter[]               $filters
     */
    public function __construct(array $subSets, TicketMatcher $ticketMatcher, array $filters)
    {
        $this->subSets       = $subSets;
        $this->ticketMatcher = $ticketMatcher;
        $this->filters       = MapUtils::rekeyByProperty($filters, 'id');
    }

    /**
     * @param TicketChange $ticketChange
     * @param FilterOp[]   $filterOps    Pre-computed filter operations keyed by filter ID. (Optimisation)
     *
     * @return NotifyList
     */
    public function getNotifyList(TicketChange $ticketChange, array $filterOps = [])
    {
        $list = new NotifyList();

        foreach ($this->subSets as $set) {
            if (!$set->agent->canViewTicket($ticketChange->getTicketB())) {
                continue;
            }

            foreach ($set->subscriptions as $sub) {
                if ($this->doesSubscriptionMatch($ticketChange, $set->agent, $sub, $filterOps)) {
                    $list->addAgent($set->agent->id, $sub->type);
                }
            }
        }

        return $list;
    }

    /**
     * @param TicketChange      $ticketChange
     * @param Agent             $agent
     * @param AgentSubscription $sub
     * @param FilterOp[]        $filterOps    Pre-computed filter operations keyed by filter ID. (Optimisation)
     *
     * @return bool
     */
    private function doesSubscriptionMatch(TicketChange $ticketChange, Agent $agent, AgentSubscription $sub, array $filterOps = [])
    {
        // do simple checks first as they are cheap
        if ($this->doesSubscriptionMatchSimple($ticketChange, $agent, $sub)) {
            return true;
        }

        if ($this->doesSubscriptionMatchFilter($ticketChange, $agent, $sub, $filterOps)) {
            return true;
        }

        return false;
    }

    /**
     * @param TicketChange      $ticketChange
     * @param Agent             $agent
     * @param AgentSubscription $sub
     * @param FilterOp[]        $filterOps    Pre-computed filter operations keyed by filter ID. (Optimisation)
     *
     * @return bool
     */
    private function doesSubscriptionMatchFilter(TicketChange $ticketChange, Agent $agent, AgentSubscription $sub, array $filterOps = [])
    {
        if (empty($sub->filterEvents)) {
            return false;
        }

        $context = new Context($agent);

        foreach ($sub->filterEvents as $filterEvent) {
            $filterId   = $filterEvent->filter;
            $matchEvent = in_array(AgentFilterSubscriptionEvent::FILTER_MATCH, $filterEvent->events);
            $replyEvent = in_array(AgentFilterSubscriptionEvent::FILTER_REPLY, $filterEvent->events);

            // Only a reply sub but we dont have a new reply
            if (!$matchEvent && !$ticketChange->isNewMessage()) {
                continue;
            }

            // Check if we already have a pre-computed match
            if (isset($filterOps[$filterId])) {
                $ops = $filterOps[$filterId];
                if ($replyEvent && ($ops->agentHasBeforeMatch($agent->id) || $ops->agentHasAfterMatch($agent->id))) {
                    return true;
                }
                if ($matchEvent && $ops->agentHasAddOp($agent->id)) {
                    return true;
                }

                return false;
            }

            // We dont have the filter, so its an invalid sub
            if (!isset($this->filters[$filterId])) {
                return false;
            }

            $filter     = $this->filters[$filterId];
            $matchAfter = $this->ticketMatcher->doesQueryMatch($filter->query, $ticketChange->getTicketB(), $context);

            if ($matchEvent) {
                return $matchAfter;
            }

            if ($replyEvent && $ticketChange->isNewMessage()) {
                if ($replyEvent && $matchAfter) {
                    return true;
                }

                return $this->ticketMatcher->doesQueryMatch($filter->query, $ticketChange->getTicketA(), $context);
            }
        }

        return false;
    }

    /**
     * Check if a key subscription matches.
     *
     * @param TicketChange      $ticketChange
     * @param Agent             $agent
     * @param AgentSubscription $sub
     *
     * @return bool
     */
    private function doesSubscriptionMatchSimple(TicketChange $ticketChange, Agent $agent, AgentSubscription $sub)
    {
        $events = array_fill_keys($sub->events, true);

        // Any new ticket
        if (
            isset($events[AgentSubscription::NEW_ANY])
            && $ticketChange->isNewTicket()
        ) {
            return true;
        }

        // Agent assigned
        if (
            isset($events[AgentSubscription::ASSIGN_ME])
            && $ticketChange->hasChange('ticket.agent')
            && $ticketChange->getTicketB()->agent == $agent->id
        ) {
            return true;
        }

        // Team assigned
        if (
            isset($events[AgentSubscription::ASSIGN_MY_TEAM])
            && $ticketChange->hasChange('ticket.agent_team')
            && !empty($agent->teams)
            && in_array($ticketChange->getTicketB()->agent_team, $agent->teams)
        ) {
            return true;
        }

        // Added as follower
        if (
            isset($events[AgentSubscription::ASSIGN_FOLLOW])
            && $ticketChange->hasChange('ticket.followers')
            && in_array($agent->id, $ticketChange->getNewFollowers())
        ) {
            return true;
        }

        if ($ticketChange->isNewMessage()) {
            // Any new reply
            if (isset($events[AgentSubscription::REPLY_ANY])) {
                return true;
            }

            // Reply on own tickets
            if (
                isset($events[AgentSubscription::REPLY_ME])
                && $ticketChange->getTicketB()->agent == $agent->id
            ) {
                return true;
            }

            // Reply on team tickets
            if (
                isset($events[AgentSubscription::REPLY_MY_TEAM])
                && !empty($agent->teams)
                && in_array($ticketChange->getTicketB()->agent_team, $agent->teams)
            ) {
                return true;
            }

            // Reply on tickets followed
            if (
                isset($events[AgentSubscription::REPLY_FOLLOW])
                && in_array($agent->id, $ticketChange->getTicketB()->followers)
            ) {
                return true;
            }
        }

        return false;
    }
}

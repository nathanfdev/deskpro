<?php

/**
 * DeskPRO.
 *
 * @category Tickets
 */

namespace Application\DeskPRO\Tickets\TicketMerge\Property;

/**
 * The agent does a standard right/left merge for agent and team, but offers the option of adding
 * the other agent as a follower.
 */
class Agent extends PropertyAbstract
{
    public function merge()
    {
        if ($this->strategy == self::STRATEGY_RIGHT) {
            $old_agent = $this->ticket->agent;

            $this->ticket->agent      = $this->other_ticket->agent;
            $this->ticket->agent_team = $this->other_ticket->agent_team;

            if ($this->getStrategyOption('add_follower') and $old_agent != $this->ticket->agent) {
                $this->ticket->addParticipantPerson($old_agent);
            }
        } elseif ($this->strategy == self::STRATEGY_COMBINE) {
            if (!$this->ticket->agent) {
                $this->ticket->agent = $this->other_ticket->agent;
            }
            if (!$this->ticket->agent_team) {
                $this->ticket->agent_team = $this->other_ticket->agent_team;
            }
        }
    }
}

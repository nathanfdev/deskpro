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
 * @category Tickets
 */

namespace Application\DeskPRO\Tickets\Filters;

use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\TicketFilter;

/**
 * Represents a changed filter in the FilterChangeDetector
 */
class FilterChange
{
    /**
     * @var \Application\DeskPRO\Entity\TicketFilter
     */
    private $filter;

    /**
     * @var \Application\DeskPRO\Entity\Person[]
     */
    private $added_for_agents = array();

    /**
     * @var \Application\DeskPRO\Entity\Person[]
     */
    private $removed_for_agents = array();

    /**
     * @var \Application\DeskPRO\Entity\Person[]
     */
    private $orig_match_for_agents = array();

    /**
     * @var \Application\DeskPRO\Entity\Person[]
     */
    private $new_match_for_agents = array();

    /**
     * @param TicketFilter $filter
     */
    public function __construct(TicketFilter $filter)
    {
        $this->filter = $filter;
    }

    /**
     * @return TicketFilter
     */
    public function getFilter()
    {
        return $this->filter;
    }

    /**
     * @param Person $agent
     */
    public function addForAgent(Person $agent)
    {
        $this->added_for_agents[$agent->id] = $agent;
    }

    /**
     * @param Person $agent
     */
    public function removeForAgent(Person $agent)
    {
        $this->removed_for_agents[$agent->id] = $agent;
    }

    /**
     * @param Person $agent
     */
    public function originalMatchForAgent(Person $agent)
    {
        $this->orig_match_for_agents[$agent->id] = $agent;
    }

    /**
     * @param Person $agent
     */
    public function newMatchForAgent(Person $agent)
    {
        $this->new_match_for_agents[$agent->id] = $agent;
    }

    /**
     * @return \Application\DeskPRO\Entity\Person[]
     */
    public function getAgentsAdded()
    {
        return $this->added_for_agents;
    }

    /**
     * @return \Application\DeskPRO\Entity\Person[]
     */
    public function getAgentsRemoved()
    {
        return $this->removed_for_agents;
    }

    /**
     * @return \Application\DeskPRO\Entity\Person[]
     */
    public function getAgentsWithOriginalMatch()
    {
        return $this->orig_match_for_agents;
    }

    /**
     * @return \Application\DeskPRO\Entity\Person[]
     */
    public function getAgentsWithNewMatch()
    {
        return $this->new_match_for_agents;
    }

    /**
     * @return bool
     */
    public function hasAdds()
    {
        return $this->added_for_agents ? true : false;
    }

    /**
     * @return bool
     */
    public function hasRemoved()
    {
        return $this->removed_for_agents ? true : false;
    }

    /**
     * @return bool
     */
    public function hasOriginalMatches()
    {
        return $this->orig_match_for_agents ? true : false;
    }

    /**
     * @return bool
     */
    public function hasNewMatches()
    {
        return $this->new_match_for_agents ? true : false;
    }

    /**
     * Merges another FilterChange $f with this one.
     *
     * @param FilterChange $f
     */
    public function merge(FilterChange $f)
    {
        foreach ($this->getAgentsAdded() as $a) {
            $this->addForAgent($a);
        }
        foreach ($this->getAgentsRemoved() as $a) {
            $this->removeForAgent($a);
        }
        foreach ($this->getAgentsWithNewMatch() as $a) {
            $this->newMatchForAgent($a);
        }
        foreach ($this->getAgentsWithOriginalMatch() as $a) {
            $this->originalMatchForAgent($a);
        }
    }
}

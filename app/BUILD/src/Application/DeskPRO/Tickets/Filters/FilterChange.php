<?php

/**
 * DeskPRO.
 *
 * @category Tickets
 */

namespace Application\DeskPRO\Tickets\Filters;

use Application\DeskPRO\Entity\Person;

/**
 * Represents a changed filter in the FilterChangeDetector.
 */
class FilterChange
{
    /**
     * @var array
     */
    private $filter;

    /**
     * @var \Application\DeskPRO\Entity\Person[]
     */
    private $added_for_agents = [];

    /**
     * @var \Application\DeskPRO\Entity\Person[]
     */
    private $removed_for_agents = [];

    /**
     * @var \Application\DeskPRO\Entity\Person[]
     */
    private $orig_match_for_agents = [];

    /**
     * @var \Application\DeskPRO\Entity\Person[]
     */
    private $new_match_for_agents = [];

    /**
     * @param array $filter
     */
    public function __construct(array $filter)
    {
        $this->filter = $filter;
    }

    /**
     * @return array
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
        $this->added_for_agents[$agent->getId()] = $agent;
    }

    /**
     * @param Person $agent
     */
    public function removeForAgent(Person $agent)
    {
        $this->removed_for_agents[$agent->getId()] = $agent;
    }

    /**
     * @param Person $agent
     */
    public function originalMatchForAgent(Person $agent)
    {
        $this->orig_match_for_agents[$agent->getId()] = $agent;
    }

    /**
     * @param Person $agent
     */
    public function newMatchForAgent(Person $agent)
    {
        $this->new_match_for_agents[$agent->getId()] = $agent;
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

<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\AppBundle\TermEngine\Engine;

use Application\DeskPRO\Entity\Person;
use DeskPRO\Bundle\AppBundle\Exception\UnknownTicketGroupingColumnException;
use DeskPRO\Bundle\AppBundle\Model\TicketGrouping;

/**
 * Class TermEngineContext.
 */
class TermEngineContext
{
    /**
     * @var Person
     */
    protected $agent;

    /**
     * @var [TicketGrouping]
     */
    protected $groupings;

    /**
     * Constructor.
     *
     * @param Person $agent
     */
    public function __construct(Person $agent)
    {
        $this->agent     = $agent;
        $this->groupings = [];
    }

    /**
     * @return Person
     */
    public function getAgent()
    {
        return $this->agent;
    }

    /**
     * @param Person $agent
     */
    public function setAgent(Person $agent)
    {
        $this->agent = $agent;
    }

    /**
     * Adds a group-by clause to the resulting query.
     *
     * @param TicketGrouping $group_by
     *
     * @return $this
     */
    public function addGroupBy(TicketGrouping $group_by)
    {
        $this->groupings[] = $group_by;

        return $this;
    }

    /**
     * Adds group by from a string.
     *
     * @param string $groupByString is a string of comma-separated columns to group the tickets by
     *
     * @return $this
     */
    public function addGroupByFromString($groupByString)
    {
        $groupBys = explode(',', $groupByString);
        foreach ($groupBys as $groupBy) {
            $grouping = null;
            try {
                $grouping = TicketGrouping::fromString($groupBy);
            } catch (UnknownTicketGroupingColumnException $e) {
                continue;
            }

            $this->addGroupBy($grouping);
        }

        return $this;
    }

    /**
     * public function getGroupBys.
     *
     * @return TicketGrouping[]
     */
    public function getGroupBys()
    {
        return $this->groupings;
    }
}

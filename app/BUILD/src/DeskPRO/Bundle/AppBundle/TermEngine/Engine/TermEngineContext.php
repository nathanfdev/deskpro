<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2016, DeskPRO Ltd.
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

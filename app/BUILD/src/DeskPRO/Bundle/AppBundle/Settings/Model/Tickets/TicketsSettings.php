<?php

namespace DeskPRO\Bundle\AppBundle\Settings\Model\Tickets;

/**
 * Class TicketsSettings.
 */
class TicketsSettings
{
    const FILTER_GROUPING_PREFIX = 'agent.ticket_filter.group_by.';

    /**
     * @var array [filter ID => group_by value] map
     */
    private $filterGroupings = [];

    /**
     * @return array
     */
    public function getFilterGroupings()
    {
        return $this->filterGroupings;
    }

    /**
     * @param array $filterGroupings
     */
    public function setFilterGroupings($filterGroupings)
    {
        $this->filterGroupings = $filterGroupings;
    }
}

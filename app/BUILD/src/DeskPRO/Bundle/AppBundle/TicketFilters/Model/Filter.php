<?php

namespace DeskPRO\Bundle\AppBundle\TicketFilters\Model;

use DeskPRO\Component\FilterQueryLanguage\Query\Query;

class Filter
{
    /**
     * @var int
     */
    public $id = 0;

    /**
     * Agents who can see the filter.
     *
     * @var int[]
     */
    public $agents = [];

    /**
     * @var Query
     */
    public $query;
}

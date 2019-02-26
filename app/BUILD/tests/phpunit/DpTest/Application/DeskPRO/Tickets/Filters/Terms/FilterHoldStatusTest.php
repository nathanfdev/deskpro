<?php

/**
 * DeskPRO.
 */

namespace DpTest\DeskPRO\Application\Tickets\Filters\Terms;

use Application\DeskPRO\Tickets\Filters\Terms\FilterHoldStatus;
use DpTest\DeskProTestCase;

class FilterHoldStatusTest extends DeskProTestCase
{
    /**
     * @testWith    ["is", 1, "((tickets.status = \"pending\"))"]
     *              ["is", 0, "((tickets.status != \"pending\"))"]
     *              ["not", 1, "((tickets.status != \"pending\"))"]
     *              ["not", 0, "((tickets.status = \"pending\"))"]
     */
    public function testGetFilterQuery($op, $isHold, $expectedWhere)
    {
        $filter = new FilterHoldStatus($op, ['is_hold' => $isHold]);
        $parts  = $filter->getFilterQuery()->getQueryParts();

        $this->assertEquals($expectedWhere, $parts['where']);
    }
}

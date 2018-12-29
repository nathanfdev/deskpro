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
     * @testWith    ["is", 1, "((tickets.is_hold = \"pending\"))"]
     *              ["is", 0, "((tickets.is_hold != \"pending\"))"]
     *              ["not", 1, "((tickets.is_hold != \"pending\"))"]
     *              ["not", 0, "((tickets.is_hold = \"pending\"))"]
     */
    public function testGetFilterQuery($op, $isHold, $expectedWhere)
    {
        $filter = new FilterHoldStatus($op, ['is_hold' => $isHold]);
        $parts  = $filter->getFilterQuery()->getQueryParts();

        $this->assertEquals($expectedWhere, $parts['where']);
    }
}

<?php

/**
 * DeskPRO.
 */

namespace DpTest\DeskPRO\Application\Tickets\Filters\Terms;

use Application\DeskPRO\Tickets\Filters\Terms\FilterStatus;
use DpTest\DeskProTestCase;

class FilterStatusTest extends DeskProTestCase
{
    /**
     * @testWith    ["awaiting_user", "((tickets.status IN ('awaiting_user')))"]
     *              ["hidden.2", "((tickets.status = 'hidden' AND tickets.ticket_status_id = 2))"]
     *              [["hidden.2"], "((tickets.status = 'hidden' AND tickets.ticket_status_id = 2))"]
     *              [["hidden.2", "awaiting_user", "awaiting_agent"], "((tickets.status IN ('awaiting_user','awaiting_agent')) OR (tickets.status = 'hidden' AND tickets.ticket_status_id = 2))"]
     *              [["hidden.deleted"], "((tickets.status = 'hidden' AND ticket_statuses.sys_id = 'deleted'))"]
     */
    public function testGetFilterQuery($status, $expectedWhere)
    {
        $filter = new FilterStatus('', ['status' => $status]);
        $parts  = $filter->getFilterQuery()->getQueryParts();

        $this->assertEquals($expectedWhere, $parts['where']);
    }
}

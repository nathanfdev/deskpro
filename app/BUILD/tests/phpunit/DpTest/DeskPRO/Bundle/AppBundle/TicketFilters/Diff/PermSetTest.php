<?php

namespace DpTest\Bundle\AppBundle\TicketFilters\Diff;

use DeskPRO\Bundle\AppBundle\TicketFilters\Diff\PermSets;
use DeskPRO\Bundle\AppBundle\TicketFilters\Model\Agent;
use DeskPRO\Component\Util\ListUtils;
use DeskPRO\Component\Util\MapUtils;

class PermSetTest extends \PHPUnit_Framework_TestCase
{
    public function testPermSets()
    {
        $agentContexts = [
            //all
            $this->makeAgent(1, [1, 2, 3], true, true, true),
            $this->makeAgent(2, [1, 2, 3], true, true, true),

            // dep
            $this->makeAgent(3, [1, 2, 3], false, true, true),
            $this->makeAgent(4, [1, 2, 3], false, true, true),

            // mixed dep
            $this->makeAgent(5, [1, 2, 3, 4], false, true, true),
            $this->makeAgent(6, [1, 2, 3, 5], false, true, true),

            // dep mixed perm
            $this->makeAgent(7, [1, 2, 3], false, true, false),
            $this->makeAgent(8, [1, 2, 3], false, false, true),
        ];

        $set  = new PermSets($agentContexts);
        $sets = $set->getSets();

        $setToIds = MapUtils::mapValues($sets, function ($k, $agents) {
            return ListUtils::map($agents, function ($a) {
                return $a->id;
            });
        });

        $this->assertEquals(
            [1, 2],
            $setToIds['all']
        );

        $this->assertEquals(
            [3, 4],
            $setToIds['1,2,3,other,un']
        );

        $this->assertEquals(
            [5],
            $setToIds['1,2,3,4,other,un']
        );

        $this->assertEquals(
            [6],
            $setToIds['1,2,3,5,other,un']
        );

        $this->assertEquals(
            [7],
            $setToIds['1,2,3,other']
        );

        $this->assertEquals(
            [8],
            $setToIds['1,2,3,un']
        );
    }

    private function makeAgent($id, $depids, $all, $other, $unassigned)
    {
        $agent                      = new Agent();
        $agent->id                  = $id;
        $agent->allowed_departments = $depids;
        if ($all) {
            $agent->all_departments_allowed = true;
        }
        $agent->view_assigned   = $other;
        $agent->view_unassigned = $unassigned;

        return $agent;
    }
}

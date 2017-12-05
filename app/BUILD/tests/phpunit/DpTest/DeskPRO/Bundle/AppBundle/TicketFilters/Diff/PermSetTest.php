<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2017, DeskPRO Ltd.
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

namespace DpTest\Bundle\AppBundle\TicketFilters\Diff;

use DeskPRO\Bundle\AppBundle\TicketFilters\Diff\PermSets;
use DeskPRO\Bundle\AppBundle\TicketFilters\Model\Context\AgentContext;
use DeskPRO\Component\Util\ListUtils;
use DeskPRO\Component\Util\MapUtils;

class PermSetTest extends \PHPUnit_Framework_TestCase
{
    public function testPermSets()
    {
        $agentContexts = [
            //all
            $this->makeAgentContext(1, [1, 2, 3], true, true, true),
            $this->makeAgentContext(2, [1, 2, 3], true, true, true),

            // dep
            $this->makeAgentContext(3, [1, 2, 3], false, true, true),
            $this->makeAgentContext(4, [1, 2, 3], false, true, true),

            // mixed dep
            $this->makeAgentContext(5, [1, 2, 3, 4], false, true, true),
            $this->makeAgentContext(6, [1, 2, 3, 5], false, true, true),

            // dep mixed perm
            $this->makeAgentContext(7, [1, 2, 3], false, true, false),
            $this->makeAgentContext(8, [1, 2, 3], false, false, true),
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

    private function makeAgentContext($id, $depids, $all, $other, $unassigned)
    {
        $agentContext                      = new AgentContext();
        $agentContext->id                  = $id;
        $agentContext->allowed_departments = $depids;
        $agentContext->view_all            = $all;
        $agentContext->view_assigned       = $other;
        $agentContext->view_unassigned     = $unassigned;

        return $agentContext;
    }
}

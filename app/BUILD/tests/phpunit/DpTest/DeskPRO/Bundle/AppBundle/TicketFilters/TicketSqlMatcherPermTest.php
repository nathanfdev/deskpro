<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2018, DeskPRO Ltd.
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

namespace DpTest\Bundle\AppBundle\TicketFilters;

use DeskPRO\Bundle\AppBundle\TicketFilters\Model\Agent;
use DeskPRO\Bundle\AppBundle\TicketFilters\SqlBuilder\SqlBuilder;
use DeskPRO\Bundle\AppBundle\TicketFilters\SqlBuilder\SqlConditionGroup;
use DeskPRO\Bundle\AppBundle\TicketFilters\TicketSqlMatcher;
use DpTestSrc\TestBundle\Mock\Dbal\ConnectionMock;

class TicketSqlMatcherPermTest extends \PHPUnit_Framework_TestCase
{
    public function test_all()
    {
        $agent                      = new Agent();
        $agent->view_all            = true;
        $agent->view_assigned       = true;
        $agent->view_unassigned     = true;
        $agent->allowed_departments = [50, 51, 52];
        $agent->id                  = 1;
        $agent->teams               = [1, 2, 3];

        $conds = TicketSqlMatcher::buildPermissionConditionForAgent($agent);
        $this->assertNull($conds);
    }

    public function test_none_but_own()
    {
        $agent                      = new Agent();
        $agent->view_all            = false;
        $agent->view_assigned       = true;
        $agent->view_unassigned     = true;
        $agent->allowed_departments = [50, 51, 52];
        $agent->id                  = 1;
        $agent->teams               = [1, 2, 3];

        $this->assertEqualSqlForAgent($agent, '
            SELECT tickets.id
            FROM tickets tickets
            WHERE ((tickets.agent_id = :c0) OR (tickets.agent_team_id IN (:c1))) OR ((tickets.department_id IN (:c2)))
        ', [
            'c0' => 1,
            'c1' => [1, 2, 3],
            'c2' => [50, 51, 52],
        ]);
    }

    public function test_none_but_own2()
    {
        $agent                      = new Agent();
        $agent->view_all            = false;
        $agent->view_assigned       = false;
        $agent->view_unassigned     = false;
        $agent->allowed_departments = [50, 51, 52];
        $agent->id                  = 1;
        $agent->teams               = [1, 2, 3];

        $this->assertEqualSqlForAgent($agent, '
            SELECT tickets.id
            FROM tickets tickets
            WHERE ((tickets.agent_id = :c0) OR (tickets.agent_team_id IN (:c1))) OR ((tickets.department_id IN (:c2))
            AND ((tickets.agent_id IS NOT NULL OR tickets.agent_team_id IS NOT NULL)) AND ((tickets.agent_id IS NULL OR tickets.agent_team_id IS NULL)))
        ', [
            'c0' => 1,
            'c1' => [1, 2, 3],
            'c2' => [50, 51, 52],
        ]);
    }

    public function test_none_but_own_noteam()
    {
        $agent                      = new Agent();
        $agent->view_all            = false;
        $agent->view_assigned       = true;
        $agent->view_unassigned     = true;
        $agent->allowed_departments = [50, 51, 52];
        $agent->id                  = 1;
        $agent->teams               = [];

        $this->assertEqualSqlForAgent($agent, '
            SELECT tickets.id
            FROM tickets tickets
            WHERE ((tickets.agent_id = :c0)) OR ((tickets.department_id IN (:c1)))
        ', [
            'c0' => 1,
            'c1' => [50, 51, 52],
        ]);
    }

    public function test_none_but_own2_noteam()
    {
        $agent                      = new Agent();
        $agent->view_all            = false;
        $agent->view_assigned       = false;
        $agent->view_unassigned     = false;
        $agent->allowed_departments = [50, 51, 52];
        $agent->id                  = 1;
        $agent->teams               = [];

        $this->assertEqualSqlForAgent($agent, '
            SELECT tickets.id
            FROM tickets tickets
            WHERE ((tickets.agent_id = :c0)) OR ((tickets.department_id IN (:c1))
            AND ((tickets.agent_id IS NOT NULL OR tickets.agent_team_id IS NOT NULL)) AND ((tickets.agent_id IS NULL OR tickets.agent_team_id IS NULL)))
        ', [
            'c0' => 1,
            'c1' => [50, 51, 52],
        ]);
    }

    public function test_none_but_own_nodeps()
    {
        $agent                      = new Agent();
        $agent->view_all            = false;
        $agent->view_assigned       = true;
        $agent->view_unassigned     = true;
        $agent->allowed_departments = [];
        $agent->id                  = 1;
        $agent->teams               = [1, 2, 3];

        $this->assertEqualSqlForAgent($agent, '
            SELECT tickets.id
            FROM tickets tickets
            WHERE ((tickets.agent_id = :c0) OR (tickets.agent_team_id IN (:c1)))
        ', [
            'c0' => 1,
            'c1' => [1, 2, 3],
        ]);
    }

    /**
     * @param Agent  $agent
     * @param string $expectedSql
     */
    private function assertEqualSqlForAgent(Agent $agent, $expectedSql, $expectedParams = [])
    {
        $conds = TicketSqlMatcher::buildPermissionConditionForAgent($agent);
        $this->assertEqualSqlWithCond($conds, $expectedSql, $expectedParams);
    }

    /**
     * @param SqlConditionGroup $condGroup
     * @param string            $expectedSql
     */
    private function assertEqualSqlWithCond(SqlConditionGroup $condGroup, $expectedSql, $expectedParams = [])
    {
        $qb = new SqlBuilder(ConnectionMock::create());
        $qb->select('tickets.id');
        $qb->from('tickets', 'tickets');
        $qb->setMainTableAlias('tickets');
        $qb->addQueryConditionGroup($condGroup);
        $realSql = $qb->getSQL();

        $expectedSql = $this->normalizeForCmp($expectedSql);
        $realSql     = $this->normalizeForCmp($realSql);

        $this->assertEquals($expectedSql, $realSql);

        $realParams = [];
        foreach ($qb->getParameters() as $name => $val) {
            $name              = preg_replace('#_.*?$#', '', $name);
            $realParams[$name] = $val;
        }

        $this->assertEquals($expectedParams, $realParams);
    }

    /**
     * @param string $sql
     *
     * @return string
     */
    private function normalizeForCmp($sql)
    {
        $sql = preg_replace('/\s+/', ' ', $sql);
        $sql = preg_replace('#(:c\d+)_.*?\b#', '$1', $sql);

        return trim($sql);
    }
}

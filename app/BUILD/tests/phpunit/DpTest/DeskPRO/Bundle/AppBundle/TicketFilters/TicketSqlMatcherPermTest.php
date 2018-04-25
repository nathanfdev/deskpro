<?php

namespace DpTest\Bundle\AppBundle\TicketFilters;

use DeskPRO\Bundle\AppBundle\TicketFilters\Model\Agent;
use DeskPRO\Bundle\AppBundle\TicketFilters\SqlBuilder\SqlBuilder;
use DeskPRO\Bundle\AppBundle\TicketFilters\SqlBuilder\SqlCondition;
use DeskPRO\Bundle\AppBundle\TicketFilters\SqlBuilder\SqlConditionGroup;
use DeskPRO\Bundle\AppBundle\TicketFilters\TicketSqlMatcher;
use DpTestSrc\TestBundle\Mock\Dbal\ConnectionMock;

class TicketSqlMatcherPermTest extends \PHPUnit_Framework_TestCase
{
    public function test_all()
    {
        $agent                          = new Agent();
        $agent->all_departments_allowed = true;
        $agent->view_assigned           = true;
        $agent->view_unassigned         = true;
        $agent->allowed_departments     = [50, 51, 52];
        $agent->id                      = 1;
        $agent->teams                   = [1, 2, 3];

        $conds = TicketSqlMatcher::buildPermissionConditionForAgent($agent);
        $this->assertNull($conds);
    }

    public function test_any_unassigned()
    {
        $agent                          = new Agent();
        $agent->all_departments_allowed = true;
        $agent->view_assigned           = false;
        $agent->view_unassigned         = true;
        $agent->allowed_departments     = [50, 51, 52];
        $agent->id                      = 1;
        $agent->teams                   = [1, 2, 3];

        $this->assertEqualSqlForAgent($agent, '
            SELECT tickets.id FROM tickets tickets
            WHERE
            (
                ((tickets.agent_id = :c0) OR (tickets.agent_team_id IN (:c1)))
                OR
                ((tickets.agent_id IS NULL OR tickets.agent_team_id IS NULL))
            ) AND (tickets.department_id = 1)
        ', [
            'c0' => 1,
            'c1' => [1, 2, 3],
        ]);
    }

    public function test_none_but_own()
    {
        $agent                      = new Agent();
        $agent->view_assigned       = true;
        $agent->view_unassigned     = true;
        $agent->allowed_departments = [50, 51, 52];
        $agent->id                  = 1;
        $agent->teams               = [1, 2, 3];

        $this->assertEqualSqlForAgent($agent, '
            SELECT tickets.id FROM tickets tickets
            WHERE
            (
                ((tickets.agent_id = :c0) OR (tickets.agent_team_id IN (:c1)))
                OR (tickets.department_id IN (:c2))
            ) AND (tickets.department_id = 1)
        ', [
            'c0' => 1,
            'c1' => [1, 2, 3],
            'c2' => [50, 51, 52],
        ]);
    }

    public function test_none_but_own2()
    {
        $agent                      = new Agent();
        $agent->view_assigned       = false;
        $agent->view_unassigned     = false;
        $agent->allowed_departments = [50, 51, 52];
        $agent->id                  = 1;
        $agent->teams               = [1, 2, 3];

        $this->assertEqualSqlForAgent($agent, '
            SELECT tickets.id FROM tickets tickets
            WHERE
            (
                ((tickets.agent_id = :c0) OR (tickets.agent_team_id IN (:c1)))
                OR (
                    (tickets.department_id IN (:c2))
                    AND ((tickets.agent_id IS NOT NULL OR tickets.agent_team_id IS NOT NULL))
                    AND ((tickets.agent_id IS NULL OR tickets.agent_team_id IS NULL))
                )
            ) AND (tickets.department_id = 1)
        ', [
            'c0' => 1,
            'c1' => [1, 2, 3],
            'c2' => [50, 51, 52],
        ]);
    }

    public function test_none_but_own_noteam()
    {
        $agent                      = new Agent();
        $agent->view_assigned       = true;
        $agent->view_unassigned     = true;
        $agent->allowed_departments = [50, 51, 52];
        $agent->id                  = 1;
        $agent->teams               = [];

        $this->assertEqualSqlForAgent($agent, '
            SELECT tickets.id FROM tickets tickets
            WHERE
            (
                (tickets.agent_id = :c0)
                OR (tickets.department_id IN (:c1))
            ) AND (tickets.department_id = 1)
        ', [
            'c0' => 1,
            'c1' => [50, 51, 52],
        ]);
    }

    public function test_none_but_own2_noteam()
    {
        $agent                      = new Agent();
        $agent->view_assigned       = false;
        $agent->view_unassigned     = false;
        $agent->allowed_departments = [50, 51, 52];
        $agent->id                  = 1;
        $agent->teams               = [];

        $this->assertEqualSqlForAgent($agent, '
            SELECT tickets.id FROM tickets tickets
            WHERE
            (
                (tickets.agent_id = :c0)
                OR (
                    (tickets.department_id IN (:c1))
                    AND ((tickets.agent_id IS NOT NULL OR tickets.agent_team_id IS NOT NULL))
                    AND ((tickets.agent_id IS NULL OR tickets.agent_team_id IS NULL))
                )
            ) AND (tickets.department_id = 1)
        ', [
            'c0' => 1,
            'c1' => [50, 51, 52],
        ]);
    }

    public function test_none_but_own_nodeps()
    {
        $agent                      = new Agent();
        $agent->view_assigned       = true;
        $agent->view_unassigned     = true;
        $agent->allowed_departments = [];
        $agent->id                  = 1;
        $agent->teams               = [1, 2, 3];

        $this->assertEqualSqlForAgent($agent, '
            SELECT tickets.id FROM tickets tickets
            WHERE
            (
                (tickets.agent_id = :c0) OR
                (tickets.agent_team_id IN (:c1))
            ) AND (tickets.department_id = 1)
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

        // add a criteria group just so we can make sure
        // everything looks ok with criteria added, not just the per criteria
        $criteriaGroup = SqlConditionGroup::createAndGroup()
            ->addCondition(SqlCondition::create()->setWhere('{tickets}.department_id = 1'));

        $qb->addQueryConditionGroup($criteriaGroup);

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
        $sql = str_replace(['(', ')'], [' ( ', ' ) '], $sql);
        $sql = preg_replace('/\s+/', ' ', $sql);
        $sql = preg_replace('#(:c\d+)_.*?\b#', '$1', $sql);

        return trim($sql);
    }
}

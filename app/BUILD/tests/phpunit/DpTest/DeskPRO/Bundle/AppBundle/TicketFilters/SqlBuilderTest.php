<?php

namespace DpTest\Bundle\AppBundle\TicketFilters;

use DeskPRO\Bundle\AppBundle\TicketFilters\SqlBuilder\SqlBuilder;
use DeskPRO\Bundle\AppBundle\TicketFilters\SqlBuilder\SqlCondition;
use DpTest\ApiTestCase;

class SqlBuilderTest extends ApiTestCase
{
    public function test_plain_part()
    {
        $qb = new SqlBuilder($this->getContainer()->get('database_connection'));
        $qb->from('tickets', 't');
        $qb->select('COUNT(*)');
        $part = new SqlCondition();
        $part->setParam('foo', 'bar');
        $part->setWhere('{from}.foo = :foo');

        $qb->addQueryCondition($part);
        $sql = $qb->getSQL();

        $this->assertEquals(
            $this->normalizeForCmp('SELECT COUNT(*) FROM tickets t WHERE t.foo = :c0'),
            $this->normalizeForCmp($sql)
        );
    }

    public function test_joins()
    {
        $qb = new SqlBuilder($this->getContainer()->get('database_connection'));
        $qb->from('tickets', 't');
        $qb->select('COUNT(*)');

        $part = new SqlCondition();
        $part->addUniqueJoin('from', 'tickets_messages', 'm', '{m}.ticket_id = {from}.id');
        $part->setParam('foo', 'bar');
        $part->setWhere('{from}.foo = :foo');

        $qb->addQueryCondition($part);
        $sql = $qb->getSQL();

        $this->assertEquals(
            $this->normalizeForCmp('SELECT COUNT(*) FROM tickets t LEFT JOIN tickets_messages c0_m ON c0_m.ticket_id = t.id WHERE t.foo = :c1'),
            $this->normalizeForCmp($sql)
        );
    }

    public function test_multi_joins()
    {
        $qb = new SqlBuilder($this->getContainer()->get('database_connection'));
        $qb->from('tickets', 't');
        $qb->select('COUNT(*)');

        $part = new SqlCondition();
        $part->addUniqueJoin('from', 'tickets_messages', 'm', '{m}.ticket_id = {from}.id');
        $part->setParam('foo', 'bar');
        $part->setWhere('{from}.foo = :foo');
        $qb->addQueryCondition($part);

        $part = new SqlCondition();
        $part->addUniqueJoin('from', 'people', 'agent', '{agent}.person_id = {from}.agent_id');
        $part->setParam('agent_name', 'John');
        $part->setWhere('{agent}.name = :agent_name');
        $qb->addQueryCondition($part);

        $sql = $qb->getSQL();

        $this->assertEquals(
            $this->normalizeForCmp('
                SELECT COUNT(*)
                FROM tickets t
                LEFT JOIN tickets_messages c0_m ON c0_m.ticket_id = t.id
                LEFT JOIN people c1_agent ON c1_agent.person_id = t.agent_id
                WHERE
                    (t.foo = :c1_foo)
                    AND (c1_agent.name = :c2_agent_name)
            '),
            $this->normalizeForCmp($sql)
        );
    }

    public function test_with_rollup()
    {
        $qb = new SqlBuilder($this->getContainer()->get('database_connection'));
        $qb->from('tickets', 't');
        $qb->select('COUNT(*)');
        $part = new SqlCondition();
        $part->setParam('foo', 'bar');
        $part->setWhere('{from}.foo = :foo');

        $qb->addQueryCondition($part);
        $qb->addGroupBy('t.agent_id');
        $qb->addGroupBy('t.department_id');
        $qb->enableWithRollup();
        $qb->setMaxResults(10);
        $sql = $qb->getSQL();

        $this->assertEquals(
            $this->normalizeForCmp('
                SELECT COUNT(*)
                FROM tickets t
                WHERE t.foo = :c0
                GROUP BY t.agent_id, t.department_id WITH ROLLUP
                LIMIT 10
             '),
            $this->normalizeForCmp($sql)
        );
    }

    private function normalizeForCmp($sql)
    {
        $sql = str_replace(['(', ')'], [' ( ', ' ) '], $sql);
        $sql = preg_replace('/\s+/', ' ', $sql);
        $sql = preg_replace('#(:c\d+)_.*?\b#', '$1', $sql);

        return trim($sql);
    }
}

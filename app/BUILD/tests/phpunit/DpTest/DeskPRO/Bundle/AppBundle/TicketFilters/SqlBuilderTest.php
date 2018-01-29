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
            $this->normalizeForCmp('SELECT COUNT(*) FROM tickets t LEFT JOIN tickets_messages c0_m ON c0_m.ticket_id = t.id WHERE t.foo = :c0'),
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
                    (t.foo = :c0_foo)
                    AND (c1_agent.name = :c1_agent_name)
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

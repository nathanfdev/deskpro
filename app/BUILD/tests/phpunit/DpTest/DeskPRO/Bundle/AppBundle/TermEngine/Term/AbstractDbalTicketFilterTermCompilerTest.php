<?php

/**
 * DeskPRO.
 */

namespace DpTest\DeskPRO\Bundle\AppBundle\TermEngine\Term;

use DeskPRO\Bundle\AppBundle\TermEngine\Engine\Dbal\Query\DbalQueryPart;
use DpTest\ApiTestCase;

abstract class AbstractDbalTicketFilterTermCompilerTest extends ApiTestCase
{
    protected function assertWhere(DbalQueryPart $query_part, $where_string)
    {
        $this->assertSame($where_string, $query_part->getWhereString(), 'query where string match');
    }

    protected function assertParameters(DbalQueryPart $query_part, array $expected_parameters)
    {
        $this->assertEquals($expected_parameters, $query_part->getParameters(), 'query parameters match');
    }

    protected function assertNoParameters(DbalQueryPart $query_part)
    {
        $this->assertCount(0, $query_part->getParameters(), 'query has no parameters');
    }

    protected function assertNoJoins(DbalQueryPart $query_part)
    {
        $this->assertCount(0, $query_part->getJoins(), 'query has no joins');
    }

    protected function assertNoUniqueJoins(DbalQueryPart $query_part)
    {
        $this->assertCount(0, $query_part->getUniqueJoins(), 'query has no unique joins');
    }

    protected function assertJoins(DbalQueryPart $query_part, array $joins)
    {
        $this->assertEquals($joins, $query_part->getJoins(), 'query joins match expected');
    }

    protected function assertUniqueJoins(DbalQueryPart $query_part, array $unique_joins)
    {
        $this->assertEquals($unique_joins, $query_part->getUniqueJoins(), 'query unique joins match expected');
    }
}

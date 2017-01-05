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

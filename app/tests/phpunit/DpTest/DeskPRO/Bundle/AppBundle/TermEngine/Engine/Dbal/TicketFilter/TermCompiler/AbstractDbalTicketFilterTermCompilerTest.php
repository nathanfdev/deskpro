<?php
/**************************************************************************\
 * | DeskPRO (r) has been developed by DeskPRO Ltd. http://www.deskpro.com/   |
 * | a British company located in London, England.                            |
 * |                                                                          |
 * | All source code and content Copyright (c) 2012, DeskPRO Ltd.             |
 * |                                                                          |
 * | The license agreement under which this software is released              |
 * | can be found at http://www.deskpro.com/license                           |
 * |                                                                          |
 * | By using this software, you acknowledge having read the license          |
 * | and agree to be bound thereby.                                           |
 * |                                                                          |
 * | Please note that DeskPRO is not free software. We release the full       |
 * | source code for our software because we trust our users to pay us for    |
 * | the huge investment in time and energy that has gone into both creating  |
 * | this software and supporting our customers. By providing the source code |
 * | we preserve our customers' ability to modify, audit and learn from our   |
 * | work. We have been developing DeskPRO since 2001, please help us make it |
 * | another decade.                                                          |
 * |                                                                          |
 * | Like the work you see? Think you could make it better? We are always     |
 * | looking for great developers to join us: http://www.deskpro.com/jobs/    |
 * |                                                                          |
 * | ~ Thanks, Everyone at Team DeskPRO                                       |
 * \**************************************************************************/

/**
 * DeskPRO
 *
 * @package DeskPRO
 */

namespace DpTest\DeskPRO\Bundle\AppBundle\TermEngine\Engine\Dbal\TicketFilter\TermCompiler;


use DeskPRO\Bundle\AppBundle\TermEngine\Engine\Dbal\Query\DbalCompiledQuery;
use DeskPRO\Bundle\AppBundle\TermEngine\TermInterface;
use DpTest\DeskProTestCase;

abstract class AbstractDbalTicketFilterTermCompilerTest extends DeskProTestCase
{
    /**
     * @return \DeskPRO\Bundle\AppBundle\TermEngine\Engine\Dbal\TicketFilter\Compiler\DbalTicketFilterCompiler
     */
    protected function getDbalTicketFilterTermEngineCompiler()
    {
        return $this->getApiContainer()->get('term_engine.dbal_ticket_filters.compiler');
    }

    /**
     * @param $term
     * @return \DeskPRO\Bundle\AppBundle\TermEngine\Engine\Dbal\Query\DbalCompiledQuery
     */
    protected function compileTerm(TermInterface $term)
    {
        $compiled_query = $this->getDbalTicketFilterTermEngineCompiler()->compile($term);

        return $compiled_query;
    }

    /**
     * A shortcut to test lots of the query at once
     *
     * @param DbalCompiledQuery $query
     * @param $where_string
     * @param array $parameters
     * @param null $join_string
     * @param null $unique_join_string
     */
    protected function assertCompiledQuery(
        DbalCompiledQuery $query,
        $where_string,
        array $parameters = array(),
        $join_string = null,
        $unique_join_string = null
    )
    {
        $this->assertQueryWhereString($where_string, $query);
        $this->assertParameters($parameters, $query);
        if ($join_string) {
            $this->assertJoinString($join_string, $query);
        }
        if ($unique_join_string) {
            $this->assertUniqueJoinString($unique_join_string, $query);
        }
    }

    protected function assertQueryWhereString($where, DbalCompiledQuery $compiled_query)
    {
        // assert where string
        $this->assertSame(
            $where,
            $compiled_query->generateWhereString(),
            'DbalCompiledQuery WHERE clause is correct'
        );
    }

    protected function assertJoinString($join_string, DbalCompiledQuery $compiled_query)
    {
        // assert where string
        $this->assertSame(
            $join_string,
            $compiled_query->generateJoinString(),
            'DbalCompiledQuery JOIN string is correct'
        );
    }

    protected function assertUniqueJoinString($join_string, DbalCompiledQuery $compiled_query)
    {
        // assert where string
        $this->assertSame(
            $join_string,
            $compiled_query->generateUniqueJoinString(),
            'DbalCompiledQuery UNIQUE JOIN string is correct'
        );
    }

    protected function assertParameters($parameters, DbalCompiledQuery $compiled_query)
    {
        // assert the parameter values
        $this->assertEquals(
            $parameters,
            $compiled_query->getParameters(),
            'parameters are as expected'
        );
    }
}

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


use DeskPRO\Bundle\AppBundle\TermEngine\Engine\Dbal\Query\DbalQuery;
use DeskPRO\Bundle\AppBundle\TermEngine\TermInterface;
use DpTest\ApiTestCase;

abstract class AbstractDbalTicketFilterTermCompilerTest extends ApiTestCase
{
    /**
     * @return \DeskPRO\Bundle\AppBundle\TermEngine\Engine\Dbal\TicketFilter\Compiler\DbalTicketFilterCompiler
     */
    protected function getDbalTicketFilterTermEngineCompiler()
    {
        return $this->getContainer()->get('term_engine.dbal_ticket_filters.compiler');
    }

    /**
     * @param $term
     * @return \DeskPRO\Bundle\AppBundle\TermEngine\Engine\Dbal\Query\DbalQuery
     */
    protected function compileTerm(TermInterface $term)
    {
        $compiled_query = $this->getDbalTicketFilterTermEngineCompiler()->compile($term);

        return $compiled_query;
    }

    /**
     * A shortcut to test lots of the query at once
     *
     * @param DbalQuery $query
     * @param $where_string
     * @param array $parameters
     * @param null $join_string
     * @param null $unique_join_string
     */
    protected function assertCompiledQuery(
        DbalQuery $query,
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

    protected function assertQueryWhereString($where, DbalQuery $compiled_query)
    {
        // assert where string
        $this->assertSame(
            $where,
            $compiled_query->generateWhereString(),
            'DbalQuery WHERE clause is correct'
        );
    }

    protected function assertJoinString($join_string, DbalQuery $compiled_query)
    {
        // assert where string
        $this->assertSame(
            $join_string,
            $compiled_query->generateJoinString(),
            'DbalQuery JOIN string is correct'
        );
    }

    protected function assertUniqueJoinString($join_string, DbalQuery $compiled_query)
    {
        // assert where string
        $this->assertSame(
            $join_string,
            $compiled_query->generateUniqueJoinString(),
            'DbalQuery UNIQUE JOIN string is correct'
        );
    }

    protected function assertParameters($parameters, DbalQuery $compiled_query)
    {
        // assert the parameter values
        $this->assertEquals(
            $parameters,
            $compiled_query->getParameters(),
            'parameters are as expected'
        );
    }
}

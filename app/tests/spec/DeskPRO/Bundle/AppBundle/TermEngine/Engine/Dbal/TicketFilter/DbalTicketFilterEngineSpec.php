<?php
/**************************************************************************\
 * | DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/  |
 * | a British company located in London, England.                            |
 * |                                                                          |
 * | All source code and content Copyright (c) 2014, DeskPRO Ltd.             |
 * |                                                                          |
 * | The license agreement under which this software is released              |
 * | can be found at https://www.deskpro.com/eula/                            |
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

namespace spec\DeskPRO\Bundle\AppBundle\TermEngine\Engine\Dbal\TicketFilter;

use DeskPRO\Bundle\AppBundle\Entity\Filter;
use DeskPRO\Bundle\AppBundle\TermEngine\Engine\Dbal\DbalEngineContext;
use DeskPRO\Bundle\AppBundle\TermEngine\Engine\Dbal\DbalQueryManipulator;
use DeskPRO\Bundle\AppBundle\TermEngine\Engine\Dbal\Query\DbalCompiledQuery;
use DeskPRO\Bundle\AppBundle\TermEngine\Engine\Dbal\TicketFilter\DbalTicketFilterEngineCompiler;
use DeskPRO\Bundle\AppBundle\TermEngine\Expression\TermEngineExpressionLanguage;
use Doctrine\DBAL\Connection;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use DeskPRO\Bundle\AppBundle\TermEngine\Engine\Dbal\TicketFilter\DbalTicketFilterEngine;
use DeskPRO\Bundle\AppBundle\TermEngine\Engine\Dbal\Query\DbalExecutableQuery;

/**
 * @mixin \DeskPRO\Bundle\AppBundle\TermEngine\Engine\Dbal\TicketFilter\DbalTicketFilterEngine
 */
class DbalTicketFilterEngineSpec extends ObjectBehavior
{
    function let(
        DbalTicketFilterEngineCompiler $compiler,
        DbalQueryManipulator $query_manipulator,
        Connection $connection
    )
    {
        $this->beConstructedWith($compiler, $query_manipulator, $connection);
    }

    function it_is_a_dbal_engine()
    {
        $this->shouldBeAnInstanceOf('DeskPRO\Bundle\AppBundle\TermEngine\Engine\Dbal\DbalEngine');
    }

    function it_creates_an_executable_query_for_a_filter_using_given_context(
        Filter $filter,
        DbalEngineContext $context,
        DbalTicketFilterEngineCompiler $compiler,
        DbalQueryManipulator $query_manipulator,
        DbalCompiledQuery $compiled_query
    )
    {
        $compiler->compile($filter)->willReturn($compiled_query);

        $query_manipulator->ensureAgentPermissions($compiled_query, $context)
            ->shouldBeCalled();

        $query_manipulator->alterPagination($compiled_query, $context)
            ->shouldBeCalled();

        $query_manipulator->alterGrouping($compiled_query, $context)
            ->shouldBeCalled();

        $query_manipulator->alterSortOrder($compiled_query, $context)
            ->shouldBeCalled();

        $query_manipulator->alterWhere($compiled_query, $context)
            ->shouldBeCalled();

        $query_manipulator->resolveParameters($compiled_query, $context)
            ->shouldBeCalled();

        $executable_query = $this->evaluate($filter, $context);

        $executable_query->shouldBeAnInstanceOf(
            'DeskPRO\Bundle\AppBundle\TermEngine\Engine\Dbal\Query\DbalExecutableQuery'
        );
    }
}

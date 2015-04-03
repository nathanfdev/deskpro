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

namespace spec\DeskPRO\Bundle\AppBundle\TermEngine\Engine\Dbal\Query;

use DeskPRO\Bundle\AppBundle\TermEngine\Engine\Dbal\Query\DbalQuery;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use DeskPRO\Bundle\AppBundle\TermEngine\Engine\Dbal\Query\DbalQueryBuilder;

/**
 * @mixin \DeskPRO\Bundle\AppBundle\TermEngine\Engine\Dbal\Query\DbalQueryBuilder
 */
class DbalQueryBuilderSpec extends ObjectBehavior
{
    function let(
        DbalQuery $query
    )
    {
        $this->beConstructedWith($query);
    }

    function it_is_a_wrapper_around_a_dbal_compiled_query(
        DbalQuery $query
    )
    {
        $this->getQuery()->shouldReturn($query);
    }

    function it_writes_where(
        DbalQuery $query
    )
    {
        $query->setWherePart('ticket.id = 3')->shouldBeCalled();

        $this->setWhereString('ticket.id = 3');
    }

    function it_writes_parameters(
        DbalQuery $query
    )
    {
        $query->addParameter('name_prefix', 'value')->shouldBeCalled();

        $this->addParameter('name_prefix', 'value');
    }

    function it_writes_joins(
        DbalQuery $query
    )
    {
        $query->addJoin('table', 'alias')->shouldBeCalled();

        $this->addJoin('table', 'alias');
    }

    function it_writes_unique_joins(
        DbalQuery $query
    )
    {
        $query->addUniqueJoin('table', 'on', 'type')->shouldBeCalled();

        $this->addUniqueJoin('table', 'on', 'type');
    }

    function it_lets_you_write_from(
        DbalQuery $query
    )
    {
        $query->setFrom('table', 'alias')->shouldBeCalled();

        $this->setFrom('table', 'alias');
    }
}

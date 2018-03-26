<?php

/**
 * DeskPRO.
 */

namespace spec\DeskPRO\Bundle\AppBundle\TermEngine\Engine\Dbal\Query;

use DeskPRO\Bundle\AppBundle\TermEngine\Engine\Dbal\Query\DbalQuery;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

/**
 * @mixin \DeskPRO\Bundle\AppBundle\TermEngine\Engine\Dbal\Query\DbalCompositeQueryBuilder
 */
class DbalCompositeQueryBuilderSpec extends ObjectBehavior
{
    public function let(
        DbalQuery $query
    ) {
        $this->beConstructedWith($query);
    }

    public function it_a_dbal_query_builder()
    {
        $this->shouldHaveType('DeskPRO\Bundle\AppBundle\TermEngine\Engine\Dbal\Query\DbalQueryBuilder');
    }

    public function it_doesnt_save_where_strings_to_the_real_dbal_query(
        DbalQuery $query
    ) {
        $query->setWherePart(Argument::any())->shouldNotBeCalled();

        $this->setWhereString('ticket.id = 5');
    }

    public function it_collects_the_set_where_strings_and_stores_them_for_retrieval(
        DbalQuery $query
    ) {
        $this->setWhereString('ticket.id = 5');
        $this->setWhereString('ticket.id IN (3, 4)');

        $this->getWhereStrings()->shouldBe(
            [
                'ticket.id = 5',
                'ticket.id IN (3, 4)',
            ]
        );
    }
}

<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2016, DeskPRO Ltd.
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

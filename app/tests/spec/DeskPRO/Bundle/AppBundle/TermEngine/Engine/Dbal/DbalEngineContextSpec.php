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

namespace spec\DeskPRO\Bundle\AppBundle\TermEngine\Engine\Dbal;

use Application\DeskPRO\Entity\Person;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use DeskPRO\Bundle\AppBundle\TermEngine\Engine\Dbal\DbalEngineContext;

/**
 * @mixin \DeskPRO\Bundle\AppBundle\TermEngine\Engine\Dbal\DbalEngineContext
 */
class DbalEngineContextSpec extends ObjectBehavior
{
    function let(
        Person $person
    )
    {
        $this->beConstructedWith($person);
    }

    function it_can_be_constructed_with_an_agent(
        Person $agent
    )
    {
        $this->beConstructedWith($agent);

        $this->getAgent()->shouldBe($agent);
    }

    function it_lets_you_change_agent(
        Person $agent1,
        Person $agent2
    )
    {
        $this->beConstructedWith($agent1);

        $this->setAgent($agent2);

        $this->getAgent()->shouldBe($agent2);
    }

    function it_lets_you_control_page_settings()
    {
        $this->setPage(5);
        $this->setPerPage(10);

        $this->getPage()->shouldBe(5);
        $this->getPerPage()->shouldBe(10);
    }

    function it_has_default_page_settings_of_null_to_signal_no_limit()
    {
        $this->getPage()->shouldBe(null);
        $this->getPerPage()->shouldBe(null);
    }

    function it_will_set_page_to_1_when_per_page_is_set_and_there_is_not_page_yet()
    {
        $this->getPage()->shouldBe(null);
        $this->getPerPage()->shouldBe(null);

        $this->setPerPage(5);

        $this->getPerPage()->shouldBe(5);
        $this->getPage()->shouldBe(1);
    }

    function it_has_no_group_bys_by_default()
    {
        $this->getGroupBy()->shouldReturn(array());
    }

    function it_allows_adding_group_bys_and_normalizes_the_direction_case()
    {
        $this->addGroupBy('date');
        $this->addGroupBy('name');

        $this->getGroupBy()->shouldBe(
            array(
                'date',
                'name'
            )
        );
    }

    function it_has_no_order_bys_by_default()
    {
        $this->getOrderBy()->shouldReturn(array());
    }

    function it_allows_adding_order_bys_and_normalizes_the_direction_case()
    {
        $this->addOrderBy('date', 'asC');
        $this->addOrderBy('name', 'deSc');

        $this->getOrderBy()->shouldBe(
            array(
                'date' => 'ASC',
                'name' => 'DESC'
            )
        );
    }

    function it_lets_you_append_to_the_where_string_of_the_end_query()
    {
        $this->getAndWhere()->shouldBe(null);

        $this->setAndWhere('ticket.dpartment_id = 4');

        $this->getAndWhere()->shouldBe('ticket.dpartment_id = 4');
    }
}

<?php

/**
 * DeskPRO.
 */

namespace spec\DeskPRO\Bundle\AppBundle\Entity;

use Application\DeskPRO\Entity\Person;
use DeskPRO\Bundle\AppBundle\Entity\TicketFilter;
use DeskPRO\Bundle\AppBundle\Entity\TicketFilterView;
use PhpSpec\ObjectBehavior;

/**
 * @mixin \DeskPRO\Bundle\AppBundle\Entity\TicketFilterPreference
 */
class TicketFilterPreferenceSpec extends ObjectBehavior
{
    public function it_starts_with_a_null_id()
    {
        $this->getId()->shouldBe(null);
    }

    public function it_is_used_with_a_single_filter(TicketFilter $filter)
    {
        $this->getFilter()->shouldBe(null);

        $this->setFilter($filter);

        $filter->addFilterPreference($this)->shouldHaveBeenCalled();
        $this->getFilter()->shouldBe($filter);
    }

    public function it_is_used_with_a_single_view(TicketFilterView $view)
    {
        $this->getFilterView()->shouldBe(null);

        $this->setFilterView($view);

        $this->getFilterView()->shouldBe($view);
    }

    public function it_defaults_to_shared()
    {
        $this->isPrivate()->shouldBe(false);
        $this->getAgent()->shouldBe(null);
    }

    public function it_can_be_private_to_a_single_agent_once_you_set_it(Person $agent)
    {
        $this->setAgent($agent);
        $this->getAgent()->shouldBe($agent);
        $this->isPrivate()->shouldBe(true);
    }

    public function it_has_a_display_order()
    {
        $this->getDisplayOrder()->shouldBe(0);
        $this->setDisplayOrder(5);
        $this->getDisplayOrder()->shouldBe(5);
    }

    public function it_has_a_main_grouping()
    {
        $this->getMainGrouping()->shouldReturn(null);
        $this->setMainGrouping('grouping');
        $this->getMainGrouping()->shouldReturn('grouping');
    }

    public function it_has_a_result_grouping()
    {
        $this->getResultGrouping()->shouldReturn(null);
        $this->setResultGrouping('grouping');
        $this->getResultGrouping()->shouldReturn('grouping');
    }

    public function it_can_show_sla()
    {
        $this->hasShowSla()->shouldBe(false);

        $this->setShowSla(true);

        $this->hasShowSla()->shouldBe(true);
    }
}

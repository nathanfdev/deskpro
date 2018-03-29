<?php

/**
 * DeskPRO.
 */

namespace spec\DeskPRO\Bundle\AppBundle\Entity;

use Application\DeskPRO\Entity\Person;
use DeskPRO\Bundle\AppBundle\Entity\TicketFilter;
use PhpSpec\ObjectBehavior;

/**
 * @mixin \DeskPRO\Bundle\AppBundle\Entity\TicketFilterSet
 */
class TicketFilterSetSpec extends ObjectBehavior
{
    public function it_starts_with_a_null_id()
    {
        $this->getId()->shouldBe(null);
    }

    public function it_starts_with_no_filters()
    {
        $this->getFilters()->toArray()->shouldBeLike([]);
    }

    public function it_lets_you_add_a_filter(TicketFilter $filter)
    {
        $this->addFilter($filter);

        $filter->setFilterSet($this)->shouldHaveBeenCalled();
        $this->getFilters()->toArray()->shouldBeLike([$filter]);
    }

    public function it_has_a_title()
    {
        $this->getTitle()->shouldBe(null);

        $this->setTitle('title');

        $this->getTitle()->shouldReturn('title');
    }

    public function it_has_a_display_order()
    {
        $this->getDisplayOrder()->shouldBe(0);

        $this->setDisplayOrder(40);

        $this->getDisplayOrder()->shouldBe(40);
    }

    public function it_initializes_by_not_being_a_default_filter_set()
    {
        $this->getIsDefault()->shouldBe(false);
    }

    public function it_can_be_toggled_on_and_off_default_status()
    {
        $this->setIsDefault(true);

        $this->getIsDefault()->shouldBe(true);

        $this->setIsDefault(false);

        $this->getIsDefault()->shouldBe(false);
    }

    public function it_initialized_with_no_agents_and_shared()
    {
        $this->getPrivateAgent()->shouldBe(null);
        $this->getSharedAgents()->toArray()->shouldBeLike([]);

        $this->isPrivate()->shouldBe(false);
    }

    public function it_marks_itself_private_if_a_single_agent_is_assigned(Person $agent)
    {
        $this->setPrivateAgent($agent);

        $this->getPrivateAgent($agent);
        $this->getSharedAgents()->toArray()->shouldBeLike([]);
        $this->isPrivate()->shouldBe(true);
    }

    public function it_marks_itself_shared_if_agent_is_added(Person $agent1, Person $agent2)
    {
        $this->addSharedAgent($agent1);

        $this->getSharedAgents()->toArray()->shouldBeLike([$agent1]);
        $this->getPrivateAgent()->shouldBe(null);
        $this->isPrivate()->shouldBe(false);

        $this->addSharedAgent($agent2);

        $this->getSharedAgents()->toArray()->shouldBeLike([$agent1, $agent2]);
        $this->getPrivateAgent()->shouldBe(null);
        $this->isPrivate()->shouldBe(false);
    }
}

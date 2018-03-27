<?php

/**
 * DeskPRO.
 */

namespace spec\DeskPRO\Bundle\AppBundle\Entity;

use DeskPRO\Bundle\AppBundle\Entity\TicketFilterPreference;
use DeskPRO\Bundle\AppBundle\Entity\TicketFilterSet;
use DeskPRO\Bundle\AppBundle\Entity\TicketFilterView;
use DeskPRO\Bundle\AppBundle\TermEngine\TermInterface;
use PhpSpec\ObjectBehavior;

/**
 * @mixin \DeskPRO\Bundle\AppBundle\Entity\TicketFilter
 */
class TicketFilterSpec extends ObjectBehavior
{
    public function it_starts_with_a_null_id()
    {
        $this->getId()->shouldBe(null);
    }

    public function it_constructs_with_created_and_updated_dates()
    {
        $this->getDateUpdated()->shouldBeAnInstanceOf('\DateTime');
        $this->getDateCreated()->shouldBeAnInstanceOf('\DateTime');
    }

    public function it_lets_you_change_updated_datetime()
    {
        $newDate = new \DateTime();
        $time    = $newDate->getTimestamp();

        $this->setDateUpdated($newDate);

        $this->getDateUpdated()->shouldHaveType(\DateTime::class);
        $this->getDateUpdated()->getTimestamp()->shouldBeLike($time);
    }

    public function it_belongs_to_only_one_filter_set(TicketFilterSet $set)
    {
        $this->getFilterSet()->shouldBe(null);

        $this->setFilterSet($set);

        $this->getFilterSet()->shouldBe($set);
    }

    public function it_can_be_associated_with_views(TicketFilterView $view1, TicketFilterView $view2)
    {
        $this->getFilterViews()->toArray()->shouldBeLike([]);

        $this->addFilterView($view1);
        $this->addFilterView($view2);

        $this->getFilterViews()->toArray()->shouldBeLike([$view1, $view2]);
    }

    public function it_can_be_associated_with_preferences(TicketFilterPreference $pref1, TicketFilterPreference $pref2)
    {
        $this->getFilterPreferences()->toArray()->shouldBeLike([]);

        $this->addFilterPreference($pref1);
        $this->addFilterPreference($pref2);

        $this->getFilterPreferences()->toArray()->shouldBeLike([$pref1, $pref2]);
    }

    public function it_has_a_title()
    {
        $this->getTitle()->shouldBe(null);

        $this->setTitle('title');

        $this->getTitle()->shouldBe('title');
    }

    public function it_has_a_display_order()
    {
        $this->getDisplayOrder()->shouldBe(0);

        $this->setDisplayOrder(32);

        $this->getDisplayOrder()->shouldBe(32);
    }

    public function it_holds_its_term_engine_term(
        TermInterface $term
    ) {
        $this->getTerm()->shouldBe(null);

        $this->setTerm($term);

        $this->getTerm()->shouldBe($term);
    }
}

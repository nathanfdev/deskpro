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
        $new_date = new \DateTime();

        $this->setDateUpdated($new_date);

        $this->getDateUpdated()->shouldBeLike($new_date);
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

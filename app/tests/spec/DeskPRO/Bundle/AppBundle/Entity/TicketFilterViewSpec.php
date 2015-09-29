<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2015, DeskPRO Ltd.
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

use Application\DeskPRO\Entity\Person;
use DeskPRO\Bundle\AppBundle\Entity\TicketFilter;
use DeskPRO\Bundle\AppBundle\Entity\TicketFilterView;
use PhpSpec\ObjectBehavior;

/**
 * @mixin \DeskPRO\Bundle\AppBundle\Entity\TicketFilterView
 */
class TicketFilterViewSpec extends ObjectBehavior
{
    public function it_starts_with_a_null_id()
    {
        $this->getId()->shouldBe(null);
    }

    public function it_defaults_to_list_type_so_it_always_has_a_type()
    {
        $this->getType()->shouldBe(TicketFilterView::TYPE_LIST);
    }

    public function it_allows_change_type_to_valid_type()
    {
        $this->setType(TicketFilterView::TYPE_TABLE);

        $this->getType()->shouldBe(TicketFilterView::TYPE_TABLE);

        $this->shouldThrow('\InvalidArgumentException')->during('setType', array('invalid'));
    }

    public function it_is_associated_with_a_filter(TicketFilter $filter)
    {
        $this->getFilter()->shouldBe(null);

        $this->setFilter($filter);

        $this->getFilter()->shouldBe($filter);

        $filter->addFilterView($this)->shouldHaveBeenCalled();
    }

    public function it_is_shared_by_default()
    {
        $this->getAgent()->shouldBe(null);
        $this->isPrivate()->shouldBe(false);
    }

    public function it_can_be_private_to_an_agent_simply_by_setting_one(Person $person)
    {
        $this->setAgent($person);
        $this->getAgent()->shouldBe($person);
        $this->isPrivate()->shouldBe(true);
    }

    public function it_has_fields()
    {
        $this->getFields()->shouldBe(array());
        $this->addField('id');
        $this->addField('name');
        $this->addField('email');

        $this->getFields()->shouldBe(array('id', 'name', 'email'));

        $this->removeField('name');

        $this->getFields()->shouldBe(array('id', 'email'));

        $this->setFields(array('foo', 'bar'));

        $this->getFields()->shouldBe(array('foo', 'bar'));
    }

    public function it_has_icon_fields()
    {
        $this->getIconFields()->shouldBe(array());
        $this->addIconField('id');
        $this->addIconField('name');
        $this->addIconField('email');

        $this->getIconFields()->shouldBe(array('id', 'name', 'email'));

        $this->removeIconField('name');

        $this->getIconFields()->shouldBe(array('id', 'email'));

        $this->setIconFields(array('foo', 'bar'));

        $this->getIconFields()->shouldBe(array('foo', 'bar'));
    }

    public function it_has_options()
    {
        $this->getOptions()->shouldBeLike(array());

        $this->addOption('name', 'value');
        $this->addOption('other', 'val');

        $this->getOptions()->shouldBeLike(array('name' => 'value', 'other' => 'val'));

        $this->removeOption('name');

        $this->getOptions()->shouldBeLike(array('other' => 'val'));

        $this->setOptions(array('foo' => 'bar', 'baz' => 'santa'));

        $this->getOptions()->shouldBeLike(array('foo' => 'bar', 'baz' => 'santa'));
    }
}

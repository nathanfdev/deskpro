<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2017, DeskPRO Ltd.
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

namespace spec\DeskPRO\Bundle\AppBundle\Notification\Event;

use DeskPRO\Bundle\AppBundle\Notification\Event\LegacySystemEvent;
use PhpSpec\ObjectBehavior;

/**
 * @mixin LegacySystemEvent
 */
class LegacySystemEventSpec extends ObjectBehavior
{
    public function it_returns_event_type_instead_its_name()
    {
        $this->beConstructedWith('some.event');
        $this->getName()->shouldBe('some.event');
    }

    public function it_returns_event_name_if_empty_event_type()
    {
        $this->beConstructedWith('');
        $this->getName()->shouldBe(LegacySystemEvent::EVENT_NAME);
    }

    public function it_returns_event_type_in_data()
    {
        $this->beConstructedWith('some.event', ['test.data' => 2]);
        $this->getData()->shouldBe(['test.data' => 2, 'eventType' => 'some.event']);
    }

    public function it_returns_target_if_set_in_data()
    {
        $this->beConstructedWith('some.event', ['test.data' => 2, 'target' => 1]);
        $this->getTarget()->shouldBe(1);
    }

    public function it_returns_target_null_for_target_if_not_set_in_data()
    {
        $this->beConstructedWith('some.event', ['test.data' => 2]);
        $this->getTarget()->shouldBeNull();
    }
}

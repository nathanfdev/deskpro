<?php

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
        $this->getTargets()->shouldBe([1]);
    }

    public function it_returns_target_null_for_target_if_not_set_in_data()
    {
        $this->beConstructedWith('some.event', ['test.data' => 2]);
        $this->getTargets()->shouldBe([]);
    }
}

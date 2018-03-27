<?php

namespace spec\DeskPRO\Bundle\AppBundle\Notification\Event\Ticket;

use DeskPRO\Bundle\AppBundle\Notification\Event\Ticket\TicketUpdatedEvent;
use PhpSpec\ObjectBehavior;

/**
 * @mixin TicketUpdatedEvent
 */
class TicketUpdatedEventSpec extends ObjectBehavior
{
    public function let()
    {
        $this->beConstructedWith('some.event', ['ticket_id' => 1, 'test_data' => 2]);
    }

    public function it_can_return_ticket_id()
    {
        $this->getTicketId()->shouldBe(1);
    }

    public function it_can_return_data()
    {
        $this->getData()->shouldBe(['ticket_id' => 1, 'test_data' => 2, 'eventType' => 'some.event']);
    }

    public function it_returns_propper_event_name()
    {
        $this->getName()->shouldBe('some.event');
    }
}

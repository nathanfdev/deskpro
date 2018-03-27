<?php

namespace spec\DeskPRO\Bundle\AppBundle\Notification\Event\AgentChat;

use DeskPRO\Bundle\AppBundle\Notification\Event\AgentChat\NewMessageEvent;
use PhpSpec\ObjectBehavior;

/**
 * @mixin NewMessageEvent
 */
class NewMessageEventSpec extends ObjectBehavior
{
    public function let()
    {
        $this->beConstructedWith(1);
    }

    public function it_can_return_message_id()
    {
        $this->getMessageId()->shouldBe(1);
    }

    public function it_returns_propper_event_name()
    {
        $this->getName()->shouldBe(NewMessageEvent::EVENT_NAME);
    }
}

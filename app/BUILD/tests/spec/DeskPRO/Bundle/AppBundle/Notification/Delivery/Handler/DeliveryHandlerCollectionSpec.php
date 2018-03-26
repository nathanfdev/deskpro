<?php

namespace spec\DeskPRO\Bundle\AppBundle\Notification\Delivery\Handler;

use DeskPRO\Bundle\AppBundle\Notification\Delivery\DeliveryHandlerInterface;
use DeskPRO\Bundle\AppBundle\Notification\Delivery\Handler\DeliveryHandlerCollection;
use PhpSpec\ObjectBehavior;

/**
 * @mixin DeliveryHandlerCollection
 */
class DeliveryHandlerCollectionSpec extends ObjectBehavior
{
    public function it_can_add_different_handlers(DeliveryHandlerInterface $handler)
    {
        $handler->getType()->willReturn('delivery_handler');
        $this->addHandler($handler);
        $handler->getType()->willReturn('another_deliver_handler');
        $this->addHandler($handler);
        $this->count()->shouldBe(2);
    }

    public function it_throws_an_exception_if_you_trying_to_add_same_handlers(DeliveryHandlerInterface $handler)
    {
        $handler->getType()->willReturn('delivery_handler');
        $this->addHandler($handler);
        $this->shouldThrow(new \InvalidArgumentException('Handler with type [delivery_handler] already attached!'))->during('addHandler', [$handler]);
    }

    public function it_can_remove_handler(DeliveryHandlerInterface $handler)
    {
        $handler->getType()->willReturn('delivery_handler');
        $this->addHandler($handler);
        $this->count()->shouldBe(1);
        $this->removeHandler('delivery_handler');
        $this->count()->shouldBe(0);
    }

    public function it_throws_an_exception_if_you_trying_to_remove_handler_that_not_exists(DeliveryHandlerInterface $handler)
    {
        $handler->getType()->willReturn('delivery_handler');
        $this->shouldThrow('\InvalidArgumentException')->during('removeHandler', ['delivery_handler']);
    }
}

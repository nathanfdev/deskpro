<?php

namespace spec\DeskPRO\Bundle\AppBundle\Notification\Strategy;

use DeskPRO\Bundle\AppBundle\Notification\Delivery\DeliveryService;
use DeskPRO\Bundle\AppBundle\Notification\Event\SystemEventInterface;
use DeskPRO\Bundle\AppBundle\Notification\Message\MessageInterface;
use DeskPRO\Bundle\AppBundle\Notification\NotifyHandlerCollection;
use DeskPRO\Bundle\AppBundle\Notification\NotifyHandlerInterface;
use DeskPRO\Bundle\AppBundle\Notification\Strategy\ImmediateStrategy;
use PhpSpec\ObjectBehavior;

/**
 * @mixin ImmediateStrategy
 */
class ImmediateStrategySpec extends ObjectBehavior
{
    public function let(
        NotifyHandlerCollection $notify_handler_collection,
        NotifyHandlerInterface $handler,
        SystemEventInterface $system_event,
        MessageInterface $message,
        DeliveryService $delivery_service
    ) {
        $this->beConstructedWith($notify_handler_collection);

        $handler->processEvent($system_event)->willReturn([$message]);
        $handler->getType()->willReturn('mock_handler');

        $this->attachEventHandler($handler);
        $this->setDeliveryService($delivery_service);
    }

    public function it_can_handle_system_event(
        SystemEventInterface $system_event,
        NotifyHandlerInterface $handler,
        MessageInterface $message,
        DeliveryService $delivery_service)
    {
        $this->handleSystemEvent($system_event);
        $handler->processEvent($system_event)->shouldBeCalled();
        $delivery_service->schedule($message)->shouldBeCalled();
    }
}

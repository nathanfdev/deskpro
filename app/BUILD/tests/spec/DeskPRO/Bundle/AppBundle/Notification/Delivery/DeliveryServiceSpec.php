<?php

namespace spec\DeskPRO\Bundle\AppBundle\Notification\Delivery;

use DeskPRO\Bundle\AppBundle\Notification\Delivery\DeliveryHandlerInterface;
use DeskPRO\Bundle\AppBundle\Notification\Delivery\DeliveryService;
use DeskPRO\Bundle\AppBundle\Notification\Message\MessageInterface;
use PhpSpec\ObjectBehavior;

/**
 * @mixin DeliveryService
 */
class DeliveryServiceSpec extends ObjectBehavior
{
    public function let(
        DeliveryHandlerInterface $delivery_handler,
        DeliveryHandlerInterface $another_delivery_handler,
        MessageInterface $message
    ) {
        $delivery_handler->getType()->willReturn('delivery_handler');
        $another_delivery_handler->getType()->willReturn('another_delivery_handler');

        $delivery_handler->schedule($message)->willReturn(null);
        $another_delivery_handler->schedule($message)->willReturn(null);

        $delivery_handler->deliver()->willReturn(null);
        $another_delivery_handler->deliver()->willReturn(null);
    }

    public function it_can_attach_handlers(
        DeliveryHandlerInterface $delivery_handler,
        DeliveryHandlerInterface $another_delivery_handler)
    {
        $this->attachHandler($delivery_handler);
        $this->attachHandler($another_delivery_handler);
    }

    public function it_can_detach_handlers(DeliveryHandlerInterface $delivery_handler)
    {
        $this->attachHandler($delivery_handler);
        $this->detachHandler($delivery_handler);
    }

    public function it_will_throw_an_exception_during_attaching_already_attached_handler(DeliveryHandlerInterface $delivery_handler)
    {
        $this->attachHandler($delivery_handler);
        $this->shouldThrow('\InvalidArgumentException')->duringAttachHandler($delivery_handler);
    }

    public function it_can_deliver_message_through_whole_stack_of_handlers(
        DeliveryHandlerInterface $delivery_handler,
        DeliveryHandlerInterface $another_delivery_handler,
        MessageInterface $message
    ) {
        $this->attachHandler($delivery_handler);
        $this->attachHandler($another_delivery_handler);
        $this->schedule($message);
        $delivery_handler->schedule($message)->shouldBeCalled();
        $another_delivery_handler->schedule($message)->shouldBeCalled();
        $this->deliver();
        $delivery_handler->deliver()->shouldBeCalled();
        $another_delivery_handler->deliver()->shouldBeCalled();
    }

    public function it_can_change_its_mode_when_cli(
        DeliveryHandlerInterface $delivery_handler,
        DeliveryHandlerInterface $another_delivery_handler,
        MessageInterface $message
    ) {
        $this->beConstructedWith(true); //emulate it's cli
        $this->attachHandler($delivery_handler);
        $this->attachHandler($another_delivery_handler);

        $this->startBatch(); // force batching
        $this->schedule($message);
        $delivery_handler->schedule($message)->shouldBeCalled(); // should call schedule
        $another_delivery_handler->schedule($message)->shouldBeCalled();
        $delivery_handler->deliver()->shouldNotBeCalled(); // but not deliver
        $another_delivery_handler->deliver()->shouldNotBeCalled();

        $this->stopBatch(); // after stopping batch
        $delivery_handler->deliver()->shouldBeCalled(); // deliver method should be called
        $another_delivery_handler->deliver()->shouldBeCalled();

        $this->resetBatch(); // also after reset
        $delivery_handler->deliver()->shouldBeCalled();
        $another_delivery_handler->deliver()->shouldBeCalled();
    }

    public function it_can_change_its_mode_when_web(
        DeliveryHandlerInterface $delivery_handler,
        DeliveryHandlerInterface $another_delivery_handler,
        MessageInterface $message
    ) {
        $this->beConstructedWith(false); //emulate it's web
        $this->attachHandler($delivery_handler);
        $this->attachHandler($another_delivery_handler);

        $this->schedule($message);
        $delivery_handler->schedule($message)->shouldBeCalled(); // should call schedule
        $another_delivery_handler->schedule($message)->shouldBeCalled();
        $delivery_handler->deliver()->shouldNotBeCalled(); // but not deliver (batching is enabled by default)
        $another_delivery_handler->deliver()->shouldNotBeCalled();

        $this->stopBatch(); // after stopping batch
        $delivery_handler->deliver()->shouldBeCalled(); // deliver method should be called
        $another_delivery_handler->deliver()->shouldBeCalled();

        $this->resetBatch(); // also after reset
        $delivery_handler->deliver()->shouldBeCalled();
        $another_delivery_handler->deliver()->shouldBeCalled();
    }
}

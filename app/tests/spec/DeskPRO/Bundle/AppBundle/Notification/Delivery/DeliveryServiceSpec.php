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
        DeliveryHandlerInterface $another_delivery_handler
    ) {
        $delivery_handler->getType()->willReturn('delivery_handler');
        $another_delivery_handler->getType()->willReturn('another_delivery_handler');
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
        $delivery_handler->deliver($message)->shouldBeCalled();
        $another_delivery_handler->deliver($message)->shouldBeCalled();
        $this->deliver($message);
    }
}

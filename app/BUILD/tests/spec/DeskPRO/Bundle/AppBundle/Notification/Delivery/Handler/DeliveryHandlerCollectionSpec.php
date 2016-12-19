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

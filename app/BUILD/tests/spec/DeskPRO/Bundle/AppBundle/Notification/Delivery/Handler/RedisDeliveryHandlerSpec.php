<?php

namespace spec\DeskPRO\Bundle\AppBundle\Notification\Delivery\Handler;

use DeskPRO\Bundle\AppBundle\Notification\Delivery\Handler\RedisDeliveryHandler;
use DeskPRO\Bundle\AppBundle\Notification\Message\ActionAlert;
use PhpSpec\ObjectBehavior;
use Predis\Client;
use Prophecy\Argument;

/**
 * @mixin RedisDeliveryHandler.
 */
class RedisDeliveryHandlerSpec extends ObjectBehavior
{
    public function let(Client $client)
    {
        $this->beConstructedWith($client);
        $client->publish(Argument::any(), Argument::any())->willReturn(1);

        $client->connect()->willReturn(Argument::any());
    }

    public function it_can_deliver_message(ActionAlert $actionAlert, Client $client)
    {
        $actionAlert->getTarget()->willReturn(1);
        $actionAlert->getData()->willReturn([]);
        $actionAlert->getDate()->willReturn(new \DateTime());
        $actionAlert->getType()->willReturn('test.action.alert');

        $actionAlert->getTarget()->shouldBeCalled();
        $actionAlert->getDate()->shouldBeCalled();
        $actionAlert->getId()->shouldBeCalled();
        $actionAlert->getType()->shouldBeCalled();
        $actionAlert->getData()->shouldBeCalled();
        $client->publish(RedisDeliveryHandler::CHANNEL_ACTION_ALERT, Argument::any())->shouldBeCalled();
        $this->schedule($actionAlert);
        $this->deliver();
    }
}

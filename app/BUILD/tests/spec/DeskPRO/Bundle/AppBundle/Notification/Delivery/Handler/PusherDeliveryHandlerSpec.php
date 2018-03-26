<?php

namespace spec\DeskPRO\Bundle\AppBundle\Notification\Delivery\Handler;

use Application\DeskPRO\DBAL\Connection;
use Application\DeskPRO\NewSettings\SettingsBag;
use Application\DeskPRO\NewSettings\SettingsResolver;
use DeskPRO\Bundle\AppBundle\Notification\Delivery\Handler\PusherDeliveryHandler;
use DeskPRO\Bundle\AppBundle\Notification\Message\ActionAlert;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Pusher;

/**
 * @mixin PusherDeliveryHandler.
 */
class PusherDeliveryHandlerSpec extends ObjectBehavior
{
    public function let(Pusher $pusher, SettingsResolver $resolver, SettingsBag $bag, Connection $connection)
    {
        $this->beConstructedWith($pusher, $resolver, $connection);
        $resolver->getGlobalSettings()->willReturn($bag);
        $connection->getTransactionNestingLevel()->willReturn(1);
        $bag->get('notification.settings.pusher_client.channel_prefix', '')->willReturn('');
        $bag->get('notification.settings.pusher_client.tries', 2)->willReturn(2);
    }

    public function it_can_deliver_message(ActionAlert $actionAlert, Pusher $pusher)
    {
        $actionAlert->getTarget()->willReturn(1);
        $actionAlert->getData()->willReturn([]);
        $actionAlert->getDate()->willReturn(new \DateTime());
        $actionAlert->getType()->willReturn('test.action.alert');
        $actionAlert->isBroadcast()->willReturn(false);

        $actionAlert->getTarget()->shouldBeCalled();
        $actionAlert->getDate()->shouldBeCalled();
        $actionAlert->getId()->shouldBeCalled();
        $actionAlert->getType()->shouldBeCalled();
        $actionAlert->getData()->shouldBeCalled();
        $actionAlert->isBroadcast()->shouldBeCalled();

        $pusher->triggerBatch(Argument::type('array'), true, true)->shouldBeCalled();
        $this->schedule($actionAlert);
        $this->deliver();
    }

    public function it_will_deliver_as_soon_as_possible(ActionAlert $actionAlert, Pusher $pusher)
    {
        $actionAlert->getTarget()->willReturn(1);
        $actionAlert->getData()->willReturn([]);
        $actionAlert->getDate()->willReturn(new \DateTime());
        $actionAlert->getType()->willReturn('test.action.alert');
        $actionAlert->isBroadcast()->willReturn(false);

        $actionAlert->getTarget()->shouldBeCalled();
        $actionAlert->getDate()->shouldBeCalled();
        $actionAlert->getId()->shouldBeCalled();
        $actionAlert->getType()->shouldBeCalled();
        $actionAlert->getData()->shouldBeCalled();
        $actionAlert->isBroadcast()->shouldBeCalled();

        $this->schedule($actionAlert);
        $this->deliverSoon();
        $pusher->triggerBatch(Argument::type('array'), true, true)->shouldNotBeCalled();
        $this->doDeliverSoon();
        $pusher->triggerBatch(Argument::type('array'), true, true)->shouldBeCalled();
    }
}

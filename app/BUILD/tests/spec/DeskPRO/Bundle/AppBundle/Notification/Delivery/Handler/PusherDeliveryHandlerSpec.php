<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2017, DeskPRO Ltd.
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

        $actionAlert->getTarget()->shouldBeCalled();
        $actionAlert->getDate()->shouldBeCalled();
        $actionAlert->getId()->shouldBeCalled();
        $actionAlert->getType()->shouldBeCalled();
        $actionAlert->getData()->shouldBeCalled();

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

        $actionAlert->getTarget()->shouldBeCalled();
        $actionAlert->getDate()->shouldBeCalled();
        $actionAlert->getId()->shouldBeCalled();
        $actionAlert->getType()->shouldBeCalled();
        $actionAlert->getData()->shouldBeCalled();

        $this->schedule($actionAlert);
        $this->deliverSoon();
        $pusher->triggerBatch(Argument::type('array'), true, true)->shouldNotBeCalled();
        $this->doDeliverSoon();
        $pusher->triggerBatch(Argument::type('array'), true, true)->shouldBeCalled();
    }
}

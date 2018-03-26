<?php

namespace spec\DeskPRO\Bundle\AppBundle\Notification\Delivery\Handler;

use Application\DeskPRO\DBAL\Connection;
use DeskPRO\Bundle\AppBundle\Notification\Delivery\Handler\DbDeliveryHandler;
use DeskPRO\Bundle\AppBundle\Notification\Message\ActionAlert;
use Doctrine\ORM\EntityManager;
use PhpSpec\ObjectBehavior;

/**
 * @mixin DbDeliveryHandler.
 */
class DbDeliveryHandlerSpec extends ObjectBehavior
{
    const TYPE = 'notification.delivery.handler.db';

    /**
     * @var EntityManager
     */
    protected $em;

    public function let(EntityManager $em, Connection $connection)
    {
        $em->getConnection()->willReturn($connection);
        $this->beConstructedWith($em);
    }

    public function it_can_deliver_message_through_db(ActionAlert $actionAlert, EntityManager $em)
    {
        $actionAlert->getId()->willReturn('asdf-asdf-asdf-adsf-');
        $actionAlert->getTarget()->willReturn(1);
        $actionAlert->getType()->willReturn('test.action.alert.type');
        $actionAlert->getData()->willReturn(['data' => []]);
        $actionAlert->getDate()->willReturn(date('Y-m-d H:i:s'));
        $actionAlert->isBroadcast()->willReturn(false);

        $actionAlert->getId()->shouldBeCalled();
        $actionAlert->getTarget()->shouldBeCalled();
        $actionAlert->getType()->shouldBeCalled();
        $actionAlert->getData()->shouldBeCalled();
        $actionAlert->getDate()->shouldBeCalled();
        $actionAlert->isBroadcast()->shouldBeCalled();

        $this->schedule($actionAlert);
        $this->deliver();
    }
}

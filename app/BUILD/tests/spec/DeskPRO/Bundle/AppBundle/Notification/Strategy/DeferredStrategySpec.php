<?php

namespace spec\DeskPRO\Bundle\AppBundle\Notification\Strategy;

use DeskPRO\Bundle\AppBundle\Notification\Event\SystemEventInterface;
use DeskPRO\Bundle\AppBundle\Notification\NotifyHandlerCollection;
use DeskPRO\Bundle\AppBundle\Notification\Persistance\PersistenceAdapterInterface;
use DeskPRO\Bundle\AppBundle\Notification\Strategy\DeferredStrategy;
use PhpSpec\ObjectBehavior;

/**
 * @mixin DeferredStrategy
 */
class DeferredStrategySpec extends ObjectBehavior
{
    public function let(
        NotifyHandlerCollection $notify_handler_collection,
        SystemEventInterface $system_event,
        PersistenceAdapterInterface $persistance
    ) {
        $this->beConstructedWith($notify_handler_collection);
        $this->setPersistenceAdapter($persistance);
    }

    public function it_can_handle_system_event(
        SystemEventInterface $system_event,
        PersistenceAdapterInterface $persistance)
    {
        $this->handleSystemEvent($system_event);
        $persistance->persist($system_event)->shouldBeCalled();
    }
}

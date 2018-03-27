<?php

namespace spec\DeskPRO\Bundle\AppBundle\Notification;

use DeskPRO\Bundle\AppBundle\Notification\ActionAlertHandler;
use DeskPRO\Bundle\AppBundle\Notification\Event\SystemEventInterface;
use DeskPRO\Bundle\AppBundle\Notification\Message\Generator\MessageGeneratorInterface;
use DeskPRO\Bundle\AppBundle\Notification\Message\MessageInterface;
use PhpSpec\ObjectBehavior;

/**
 * @mixin ActionAlertHandler
 */
class ActionAlertHandlerSpec extends ObjectBehavior
{
    public function let(
        MessageGeneratorInterface $generator,
        SystemEventInterface $event,
        MessageInterface $message
    ) {
        $event->getName()->willReturn('test.event');
        $generator->getType()->willReturn('message_generator');
        $generator->canCreateMessage($event)->willReturn(true);
        $generator->createMessages($event)->willReturn([$message]);
    }

    public function it_can_attach_generator(MessageGeneratorInterface $generator)
    {
        $this->attachGenerator($generator);
    }

    public function it_can_process_event(MessageGeneratorInterface $generator, SystemEventInterface $event)
    {
        $generator->canCreateMessage($event)->shouldBeCalled();
        $generator->createMessages($event)->shouldBeCalled();
        $this->attachGenerator($generator);
        $this->processEvent($event);
    }
}

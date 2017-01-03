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

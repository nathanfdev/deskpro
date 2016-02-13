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

namespace spec\DeskPRO\Bundle\AppBundle\Notification;

use DeskPRO\Bundle\AppBundle\Notification\ActionAlertHandler;
use DeskPRO\Bundle\AppBundle\Notification\NotifyHandlerCollection;
use DeskPRO\Bundle\AppBundle\Notification\UserNotificationHandler;
use PhpSpec\ObjectBehavior;

/**
 * @mixin NotifyHandlerCollection
 */
class NotifyHandlerCollectionSpec extends ObjectBehavior
{
    public function let(UserNotificationHandler $userNotificationHandler, ActionAlertHandler $actionAlertHandler)
    {
        $userNotificationHandler->getType()->willReturn('user_notification');
        $actionAlertHandler->getType()->willReturn('action_alert');
    }

    public function it_can_attach_different_handlers(
        UserNotificationHandler $userNotificationHandler,
        ActionAlertHandler $actionAlertHandler)
    {
        $this->attachHandler($userNotificationHandler);
        $this->attachHandler($actionAlertHandler);

        $this->count()->shouldBe(2);
    }

    public function it_cant_attach_handlers_with_same_type(UserNotificationHandler $handler)
    {
        $this->attachHandler($handler);
        $this->shouldThrow('\InvalidArgumentException')->during('attachHandler', [$handler]);
    }
}

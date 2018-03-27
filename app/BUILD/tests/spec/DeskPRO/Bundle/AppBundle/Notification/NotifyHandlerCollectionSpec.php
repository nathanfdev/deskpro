<?php

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

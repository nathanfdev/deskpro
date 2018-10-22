<?php

namespace spec\DeskPRO\Bundle\AppBundle\Notification;

use DeskPRO\Bundle\AppBundle\Notification\Event\AgentChat\MarkAllMessagesEvent;
use DeskPRO\Bundle\AppBundle\Notification\Event\AgentChat\MarkMessageEvent;
use DeskPRO\Bundle\AppBundle\Notification\Event\AgentChat\NewMessageEvent;
use DeskPRO\Bundle\AppBundle\Notification\Event\ExternalEvent\PopupEvent;
use DeskPRO\Bundle\AppBundle\Notification\Event\Helpdesk\RefreshAgentInterfaceEvent;
use DeskPRO\Bundle\AppBundle\Notification\Event\LegacySystemEvent;
use DeskPRO\Bundle\AppBundle\Notification\Event\Messenger\ChatEvent;
use DeskPRO\Bundle\AppBundle\Notification\Event\Messenger\ChatMessageEvent;
use DeskPRO\Bundle\AppBundle\Notification\Event\Organization\OrganizationCreatedEvent;
use DeskPRO\Bundle\AppBundle\Notification\Event\People\AgentStatusChangedEvent;
use DeskPRO\Bundle\AppBundle\Notification\Event\People\UpdateOnlineEvent;
use DeskPRO\Bundle\AppBundle\Notification\Event\Snippet\SnippetsUpdatedEvent;
use DeskPRO\Bundle\AppBundle\Notification\Event\Ticket\TicketFollowUpUpdatedEvent;
use DeskPRO\Bundle\AppBundle\Notification\Event\Ticket\TicketUpdatedEvent;
use DeskPRO\Bundle\AppBundle\Notification\Event\UserChat\UserChatEvent;
use DeskPRO\Bundle\AppBundle\Notification\NotificationEventManager;
use DeskPRO\Bundle\AppBundle\Notification\Strategy\StrategyFactory;
use PhpSpec\ObjectBehavior;

/**
 * @mixin NotificationEventManager
 */
class NotificationEventManagerSpec extends ObjectBehavior
{
    public function let(
        StrategyFactory $strategyFactory
    ) {
        $this->beConstructedWith($strategyFactory);
    }

    public function it_is_an_event_subscriber()
    {
        $this->shouldHaveType('Symfony\Component\EventDispatcher\EventSubscriberInterface');
    }

    public function it_listens_to_several_basic_events()
    {
        $this->getSubscribedEvents()->shouldBe(
            [
                NewMessageEvent::EVENT_NAME            => 'handleEvent',
                MarkMessageEvent::EVENT_NAME           => 'handleEvent',
                MarkAllMessagesEvent::EVENT_NAME       => 'handleEvent',
                TicketUpdatedEvent::EVENT_NAME         => 'handleEvent',
                UpdateOnlineEvent::EVENT_NAME          => 'handleEvent',
                AgentStatusChangedEvent::EVENT_NAME    => 'handleEvent',
                LegacySystemEvent::EVENT_NAME          => 'handleEvent',
                OrganizationCreatedEvent::EVENT_NAME   => 'handleEvent',
                UserChatEvent::EVENT_NAME              => 'handleEvent',
                RefreshAgentInterfaceEvent::EVENT_NAME => 'handleEvent',
                TicketFollowUpUpdatedEvent::EVENT_NAME => 'handleEvent',
                SnippetsUpdatedEvent::EVENT_NAME       => 'handleEvent',
                PopupEvent::EVENT_NAME                 => 'handleEvent',
                ChatEvent::EVENT_NAME                  => 'handleEvent',
                ChatMessageEvent::EVENT_NAME           => 'handleEvent',
            ]
        );
    }
}

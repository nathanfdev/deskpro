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

use DeskPRO\Bundle\AppBundle\Notification\Event\AgentChat\MarkAllMessagesEvent;
use DeskPRO\Bundle\AppBundle\Notification\Event\AgentChat\MarkMessageEvent;
use DeskPRO\Bundle\AppBundle\Notification\Event\AgentChat\NewMessageEvent;
use DeskPRO\Bundle\AppBundle\Notification\Event\Helpdesk\RefreshAgentInterfaceEvent;
use DeskPRO\Bundle\AppBundle\Notification\Event\LegacySystemEvent;
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
            ]
        );
    }
}

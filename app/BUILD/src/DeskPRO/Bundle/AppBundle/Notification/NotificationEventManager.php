<?php

namespace DeskPRO\Bundle\AppBundle\Notification;

use DeskPRO\Bundle\AppBundle\Notification\Event\AgentChat\MarkAllMessagesEvent;
use DeskPRO\Bundle\AppBundle\Notification\Event\AgentChat\MarkMessageEvent;
use DeskPRO\Bundle\AppBundle\Notification\Event\AgentChat\NewMessageEvent;
use DeskPRO\Bundle\AppBundle\Notification\Event\ExternalEvent\PopupEvent;
use DeskPRO\Bundle\AppBundle\Notification\Event\Helpdesk\RefreshAgentInterfaceEvent;
use DeskPRO\Bundle\AppBundle\Notification\Event\LegacySystemEvent;
use DeskPRO\Bundle\AppBundle\Notification\Event\Organization\OrganizationCreatedEvent;
use DeskPRO\Bundle\AppBundle\Notification\Event\People\AgentStatusChangedEvent;
use DeskPRO\Bundle\AppBundle\Notification\Event\People\UpdateOnlineEvent;
use DeskPRO\Bundle\AppBundle\Notification\Event\Snippet\SnippetsUpdatedEvent;
use DeskPRO\Bundle\AppBundle\Notification\Event\SystemEventInterface;
use DeskPRO\Bundle\AppBundle\Notification\Event\Ticket\TicketFollowUpUpdatedEvent;
use DeskPRO\Bundle\AppBundle\Notification\Event\Ticket\TicketUpdatedEvent;
use DeskPRO\Bundle\AppBundle\Notification\Event\UserChat\UserChatEvent;
use DeskPRO\Bundle\AppBundle\Notification\Strategy\NotificationStrategyInterface;
use DeskPRO\Bundle\AppBundle\Notification\Strategy\StrategyFactory;
use DeskPRO\Bundle\MessengerBundle\Notification\Event\ChatEvent;
use DeskPRO\Bundle\MessengerBundle\Notification\Event\ChatMessageEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class NotificationEventManager.
 */
class NotificationEventManager implements EventSubscriberInterface
{
    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
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
        ];
    }

    /**
     * @var StrategyFactory
     */
    protected $strategyFactory;

    /**
     * @param StrategyFactory $strategyFactory
     */
    public function __construct(StrategyFactory $strategyFactory)
    {
        $this->strategyFactory = $strategyFactory;
    }

    /**
     * @param SystemEventInterface $event
     */
    public function handleEvent(SystemEventInterface $event)
    {
        $strategy = $this->getStrategyForEvent($event);
        $strategy->handleSystemEvent($event);
    }

    /**
     * @param SystemEventInterface $event
     *
     * @return NotificationStrategyInterface
     */
    protected function getStrategyForEvent(SystemEventInterface $event)
    {
        return $this->strategyFactory->create($event);
    }

    public function deliver($postpone = false)
    {
        foreach ($this->strategyFactory->getAllBuiltStrategies() as $strategy) {
            $strategy->deliver($postpone);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function startBatch()
    {
        foreach ($this->strategyFactory->getAllBuiltStrategies() as $strategy) {
            $strategy->startBatch();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function resetBatch()
    {
        foreach ($this->strategyFactory->getAllBuiltStrategies() as $strategy) {
            $strategy->resetBatch();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function stopBatch()
    {
        foreach ($this->strategyFactory->getAllBuiltStrategies() as $strategy) {
            $strategy->stopBatch();
        }
    }
}

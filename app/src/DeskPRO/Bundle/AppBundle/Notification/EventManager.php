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

namespace DeskPRO\Bundle\AppBundle\Notification;

use DeskPRO\Bundle\AppBundle\Notification\Event\AgentChat\MarkMessageEvent;
use DeskPRO\Bundle\AppBundle\Notification\Event\AgentChat\NewMessageEvent;
use DeskPRO\Bundle\AppBundle\Notification\Event\People\UpdateOnlineEvent;
use DeskPRO\Bundle\AppBundle\Notification\Event\SystemEventInterface;
use DeskPRO\Bundle\AppBundle\Notification\Event\Ticket\TicketUpdatedEvent;
use DeskPRO\Bundle\AppBundle\Notification\Strategy\NotificationStrategyInterface;
use DeskPRO\Bundle\AppBundle\Notification\Strategy\StrategyFactory;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class EventManager.
 */
class EventManager implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return [
            NewMessageEvent::EVENT_NAME    => 'handleEvent',
            MarkMessageEvent::EVENT_NAME   => 'handleEvent',
            TicketUpdatedEvent::EVENT_NAME => 'handleEvent',
            UpdateOnlineEvent::EVENT_NAME  => 'handleEvent',
        ];
    }

    /**
     * @var StrategyFactory
     */
    protected $strategy_factory;

    /**
     * @param StrategyFactory $strategy_factory
     */
    public function __construct(StrategyFactory $strategy_factory)
    {
        $this->strategy_factory = $strategy_factory;
    }

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
        return $this->strategy_factory->create($event);
    }
}

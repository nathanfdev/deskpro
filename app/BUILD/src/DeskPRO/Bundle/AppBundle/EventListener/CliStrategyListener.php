<?php

namespace DeskPRO\Bundle\AppBundle\EventListener;

use DeskPRO\Bundle\AppBundle\Notification\Strategy\NotificationStrategyInterface;
use Symfony\Component\Console\ConsoleEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class CliStrategyListener implements EventSubscriberInterface
{
    /**
     * @var NotificationStrategyInterface[]
     */
    private $strategies = [];

    /**
     * @param NotificationStrategyInterface $strategy
     */
    public function pushStrategy(NotificationStrategyInterface $strategy)
    {
        $this->strategies[] = $strategy;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            ConsoleEvents::TERMINATE => ['onTerminate', 2],
        ];
    }

    /**
     * Deliver scheduled messages in immediate strategy.
     */
    public function onTerminate()
    {
        foreach ($this->strategies as $strategy) {
            $strategy->deliver();
        }
    }
}

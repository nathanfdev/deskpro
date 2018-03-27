<?php

namespace DeskPRO\Bundle\AppBundle\EventListener;

use DeskPRO\Bundle\AppBundle\Notification\Strategy\ImmediateStrategy;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\KernelEvents;

class ImmediateStrategyListener implements EventSubscriberInterface
{
    /**
     * @var ImmediateStrategy[]
     */
    private $strategies = [];

    /**
     * @param ImmediateStrategy $strategy
     */
    public function pushStrategy(ImmediateStrategy $strategy)
    {
        $this->strategies[] = $strategy;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::TERMINATE => ['onFinishRequest', 2],
        ];
    }

    /**
     * Deliver scheduled messages in immediate strategy.
     */
    public function onFinishRequest()
    {
        foreach ($this->strategies as $strategy) {
            $strategy->deliver();
        }
    }
}

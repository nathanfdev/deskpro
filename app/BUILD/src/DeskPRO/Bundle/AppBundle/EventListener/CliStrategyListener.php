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

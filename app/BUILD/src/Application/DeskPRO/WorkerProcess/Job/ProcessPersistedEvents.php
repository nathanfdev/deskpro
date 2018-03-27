<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\WorkerProcess\Job;

use DeskPRO\Bundle\AppBundle\Entity\Event;
use DeskPRO\Bundle\AppBundle\Notification\Strategy\DeferredStrategy;

/**
 * Updates agents online through dispatching event for action alerts.
 */
class ProcessPersistedEvents extends AbstractJob
{
    const DEFAULT_INTERVAL = 60; // 1 minute

    public function run()
    {
        if ($this->getContainer()->get('deskpro.feature_flags')->hasBeta('agent_chat')) {
            $em   = $this->getContainer()->getEm();
            $repo = $em->getRepository(Event::class);
            /** @var Event[] $events */
            $events = $repo->findBy(['processed' => false]);

            if ($events) {
                /** @var DeferredStrategy $strategy */
                $strategy = $event_dispatcher = $this->getContainer()->get('deskpro.notification.strategy_factory')->create($events[0]->getEvent());
                foreach ($events as $event) {
                    $strategy->handlePersistedEvent($event->getEvent());
                    $event->setIsPorcessed(true);
                    $em->persist($event);
                }
                $em->flush();
            }
        }
    }
}

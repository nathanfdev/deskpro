<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2016, DeskPRO Ltd.
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
        $em   = $this->getContainer()->getEm();
        $repo = $em->getRepository('\DeskPRO\Bundle\AppBundle\Entity\Event');
        /** @var Event[] $events*/
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

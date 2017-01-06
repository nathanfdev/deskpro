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

namespace DeskPRO\Bundle\AuditBundle\EventListener;

use Application\DeskPRO\Entity\CustomDataAbstract;
use Application\DeskPRO\Entity\Organization;
use DeskPRO\Bundle\AuditBundle\Event\LogEvent;
use DeskPRO\Bundle\AuditBundle\Log\ObjectCollector;

/**
 * Class DecideListener.
 */
class CollectListener
{
    /**
     * @var ObjectCollector
     */
    private $objectCollector;

    /**
     * WriteListener constructor.
     *
     * @param ObjectCollector $objectCollector
     */
    public function __construct(ObjectCollector $objectCollector)
    {
        $this->objectCollector = $objectCollector;
    }

    /**
     * @param LogEvent $event
     */
    public function onStartLog(LogEvent $event)
    {
        $entity = $event->getContext()->getEntity();
        if ($entity instanceof CustomDataAbstract) {
            $event->setShouldWrite(false);
            $this->objectCollector->addPart($event);
        }
        if ($entity instanceof Organization) {
            $this->objectCollector->addOwner($event);
        }
    }
}

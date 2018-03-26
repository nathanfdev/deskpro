<?php

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

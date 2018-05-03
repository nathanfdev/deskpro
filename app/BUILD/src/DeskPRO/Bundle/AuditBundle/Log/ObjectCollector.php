<?php

namespace DeskPRO\Bundle\AuditBundle\Log;

use Application\DeskPRO\Domain\DomainObject;
use Application\DeskPRO\Entity\CustomDataAbstract;
use DeskPRO\Bundle\AppBundle\Entity\EntityInterface;
use DeskPRO\Bundle\AuditBundle\Event\LogEvent;
use DeskPRO\Component\Util\TypeUtils;

/**
 * Class ObjectCollector.
 */
class ObjectCollector
{
    /**
     * @var array
     */
    private $collectedParts = [];

    /**
     * @var array
     */
    private $collectedOwners = [];

    /**
     * @param LogEvent $event
     */
    public function addOwner(LogEvent $event)
    {
        $owner = $event->getContext()->getEntity();
        $name  = TypeUtils::getBaseTypeName($owner);
        $id    = $owner->getId();
        if (!isset($this->collectedOwners[$name])) {
            $this->collectedOwners[$name] = [];
        }
        $this->collectedOwners[$name][$id] = $event;
    }

    /**
     * @param LogEvent $part
     */
    public function addPart(LogEvent $part)
    {
        /** @var CustomDataAbstract $entity */
        $entity = $part->getContext()->getEntity();
        /** @var EntityInterface|DomainObject $owner */
        $owner     = $entity->getOwner();
        $ownerName = TypeUtils::getBaseTypeName($owner);
        $ownerId   = $owner->getId();
        if (isset($this->collectedOwners[$ownerName])) {
            if (!isset($this->collectedParts[$ownerName])) {
                $this->collectedParts[$ownerName] = [];
            }
            $this->collectedParts[$ownerName][$ownerId][] = $part;
        }
    }

    /**
     * @return array
     */
    public function getOwners()
    {
        return $this->collectedOwners;
    }

    /**
     * @param $owner
     *
     * @return array
     */
    public function getParts($owner)
    {
        /* @var EntityInterface|DomainObject $owner */
        $ownerName = TypeUtils::getBaseTypeName($owner);
        if (isset($this->collectedParts[$ownerName]) && isset($this->collectedParts[$ownerName][$owner->getId()])) {
            return $this->collectedParts[$ownerName][$owner->getId()];
        }

        return [];
    }

    /**
     * Clear collector on flush.
     */
    public function clear()
    {
        $this->collectedOwners = [];
        $this->collectedParts  = [];
    }
}

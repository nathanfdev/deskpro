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
}

<?php

/*
 * Deskpro (r) has been developed by Deskpro Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2018, Deskpro Ltd.
 *
 * The license agreement under which this software is released
 * can be found at https://www.deskpro.com/eula/
 *
 * By using this software, you acknowledge having read the license
 * and agree to be bound thereby.
 *
 * Please note that Deskpro is not free software. We release the full
 * source code for our software because we trust our users to pay us for
 * the huge investment in time and energy that has gone into both creating
 * this software and supporting our customers. By providing the source code
 * we preserve our customers' ability to modify, audit and learn from our
 * work. We have been developing Deskpro since 2001, please help us make it
 * another decade.
 *
 * Like the work you see? Think you could make it better? We are always
 * looking for great developers to join us: http://www.deskpro.com/jobs/
 *
 * ~ Thanks, Everyone at Team Deskpro
 */

namespace DeskPRO\Bundle\AppBundle\EventListener\Doctrine;

use Application\DeskPRO\Entity\CustomDataAbstract;
use Application\DeskPRO\Entity\CustomDefAbstract;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Symfony\Component\PropertyAccess\PropertyAccess;

/**
 * Class CustomDataChangeListener.
 */
class CustomDataChangeListener
{
    /**
     * @param CustomDataAbstract $entity
     */
    public function postPersist(CustomDataAbstract $entity)
    {
        $this->recordStateChange($entity, null, $entity);
    }

    /**
     * @param CustomDataAbstract $entity
     * @param PreUpdateEventArgs $event
     */
    public function preUpdate(CustomDataAbstract $entity, PreUpdateEventArgs $event)
    {
        $oldEntity    = clone $entity;
        $propertyPath = $this->getDataProperty($entity);

        PropertyAccess::createPropertyAccessor()->setValue($oldEntity, $propertyPath, $event->getOldValue($propertyPath));
        $this->recordStateChange($entity, $oldEntity, $entity);
    }

    /**
     * @param CustomDataAbstract $entity
     */
    public function preRemove(CustomDataAbstract $entity)
    {
        $this->recordStateChange($entity, $entity, null);
    }

    /**
     * @param CustomDataAbstract $entity
     * @param CustomDataAbstract $oldEntity
     * @param CustomDataAbstract $newEntity
     */
    protected function recordStateChange(CustomDataAbstract $entity, CustomDataAbstract $oldEntity = null, CustomDataAbstract $newEntity = null)
    {
        if (!$entity->root_field) {
            return;
        }

        $defId = $entity->root_field->getId();
        $entity->getOwner()->getStateChangeRecorder()->record('custom_data.'.$defId, $oldEntity, $newEntity, true);
    }

    /**
     * @param CustomDataAbstract $entity
     *
     * @return string
     */
    protected function getDataProperty(CustomDataAbstract $entity)
    {
        switch ($entity->root_field->getType()) {
            case CustomDefAbstract::TYPE_TOGGLE:
            case CustomDefAbstract::TYPE_DATE:
            case CustomDefAbstract::TYPE_DATETIME:
            case CustomDefAbstract::TYPE_CURRENCY:
                return 'value';
            case CustomDefAbstract::TYPE_CHOICE:
                return 'field';
            default:
                return 'input';
        }
    }
}

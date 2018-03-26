<?php

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

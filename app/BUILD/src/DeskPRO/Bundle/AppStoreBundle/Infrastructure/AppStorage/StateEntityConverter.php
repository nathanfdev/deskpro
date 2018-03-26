<?php

namespace DeskPRO\Bundle\AppStoreBundle\Infrastructure\AppStorage;

use DeskPRO\Bundle\AppBundle\Entity;
use DeskPRO\Bundle\AppStoreBundle\Domain;

class StateEntityConverter
{
    /**
     * @param Entity\AppStore\AppState $stateEntity
     * @return Domain\AppStorageItem
     */
    public function toDomainObject(Entity\AppStore\AppState $stateEntity)
    {
        if (!$stateEntity->getAppInstance() || !$stateEntity->getEntityId() || !$stateEntity->getName()) {
            return null;
        }

        $entityId = Domain\AppStorage\EntityId::parse($stateEntity->getEntityId());
        if (!$entityId) {
            return null;
        }

        if (!$stateEntity->getPermRead() || !$stateEntity->getPermWrite()) {
            return null;
        }

        if (! $stateEntity->getValue()) {
            return null;
        }

        $stateIdentifier =  new Domain\AppStorageItemIdentifier(
            (string) $stateEntity->getAppInstance()->getId(),
            $stateEntity->getName(),
            $entityId
        );

        $ownerId = $stateEntity->getOwner() ? (string) $stateEntity->getOwner()->getId() : null;
        $securityDescriptor =  new Domain\AppStorage\SecurityDescriptor(
            $ownerId,
            $stateEntity->getPermRead(),
            $stateEntity->getPermWrite(),
            (bool) $stateEntity->getIsBackendOnly()
        );


        return new Domain\AppStorageItem($stateIdentifier, $securityDescriptor, $stateEntity->getValue());
    }
}

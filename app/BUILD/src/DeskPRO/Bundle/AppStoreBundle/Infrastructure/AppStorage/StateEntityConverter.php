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

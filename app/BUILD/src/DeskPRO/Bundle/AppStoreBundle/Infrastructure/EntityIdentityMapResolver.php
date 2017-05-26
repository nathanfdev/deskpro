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

namespace DeskPRO\Bundle\AppStoreBundle\Infrastructure;

use DeskPRO\Bundle\AppBundle\Entity;
use Application\DeskPRO;
use DeskPRO\Bundle\AppStoreBundle\Domain;
use Doctrine\ORM;

/**
 * This class is an implementation of the Identity Map pattern. Its role is to map from from domain objects to
 * persistence entities using only their identities
 */
class EntityIdentityMapResolver
{
    /** @var ORM\EntityManager */
    private $entityManager;

    /**
     * ApplicationService constructor.
     * @param ORM\EntityManager $entityManager
     */
    public function __construct(ORM\EntityManager $entityManager) {
        $this->entityManager = $entityManager;
    }

    /**
     * Retrieves the corresponding persistence entity for the given application domain entity
     *
     * @param Domain\Application $application
     * @return Entity\AppStore\App
     */
    public function resolveApplicationEntity(Domain\Application $application)
    {
        /** @var Entity\AppStore\App $entity */
        $entity = $application;
        if ($entity instanceof Entity\AppStore\App) {
            return $entity;

        }
        $entityId = $application->getId();
        $entity = $this->entityManager->getRepository(Entity\AppStore\App::class)->find($entityId);
        return $entity;
    }

    /**
     * @param Domain\ApplicationInstance $instance
     * @return Entity\AppStore\AppInstance
     */
    public function resolveApplicationInstance(Domain\ApplicationInstance $instance)
    {
        if ($instance instanceof Entity\AppStore\AppInstance) {
            return $instance;
        }

        $entityId = $instance->getId();
        $entity = $this->entityManager->getRepository(Entity\AppStore\AppInstance::class)->find($entityId);
        return $entity;
    }
}

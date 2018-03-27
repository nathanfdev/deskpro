<?php

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

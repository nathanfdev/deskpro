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

use Application\DeskPRO\BlobStorage\DeskproBlobStorage;
use DeskPRO\Bundle\AppBundle\Entity;
use DeskPRO\Bundle\AppStoreBundle\Domain;
use DeskPRO\Bundle\AppStoreBundle\Infrastructure;
use Doctrine\ORM;

class ApplicationManagerService implements Domain\ApplicationManager
{
    /** @var ORM\EntityManager */
    private $entityManager;

    /** @var DeskproBlobStorage */
    private $blobStorage;

    /**
     * ApplicationService constructor.
     * @param ORM\EntityManager $entityManager
     * @param DeskproBlobStorage $blobStorage
     */
    public function __construct(ORM\EntityManager $entityManager, DeskproBlobStorage $blobStorage) {
        $this->entityManager = $entityManager;
        $this->blobStorage = $blobStorage;
    }

    /**
     * @param Domain\AppBundle $bundle
     * @return Domain\ApplicationInstance
     */
    public function createFirstInstance(Domain\AppBundle $bundle)
    {
        $entities = [];
        $appEntity = $this->mapManifestStringToApp($bundle->getManifestAsString(), new Entity\AppStore\App());
        $entities[] = $appEntity;

        foreach ($bundle->listAllResources() as $resource) {
            $asset = new Entity\AppStore\AppAsset();
            $asset->setApp($appEntity);

            $entities[] = $this->mapBundleResourceToAsset($resource, $asset);
        }

        $instanceEntity = new Entity\AppStore\AppInstance();
        $instanceEntity->setApp($appEntity);
        $this->mapApplicationToInstance($appEntity, $instanceEntity);
        $entities[] = $instanceEntity;

        $this->persist($entities);
        return $instanceEntity;
    }

    /**
     * @param $entities
     */
    private function persist($entities)
    {
        foreach ($entities as $entity) {
            $this->entityManager->persist($entity);
        }


        $this->entityManager->flush();
    }

    private function mapBundleResourceToAsset(Domain\AppBundleResource $resource, Entity\AppStore\AppAsset $asset)
    {
        $asset->setPath( $resource->getPath() );
        $asset->setContent( $resource->getContent() );

        return $asset;
    }

    /**
     * @param string $manifestString
     * @param Entity\AppStore\App $app
     * @return Entity\AppStore\App
     */
    private function mapManifestStringToApp($manifestString, Entity\AppStore\App $app)
    {
        $manifestReader = new Infrastructure\AppManifestJsonReader();
        $manifest = $manifestReader->readManifest($manifestString);

        $app->setManifest($manifestString);

        $value = $manifest->getName();
        $app->setName($value);

        return $app;
    }

    /**
     * @param Domain\Application $application
     * @param string|null $settings
     * @return  Entity\AppStore\AppInstance
     */
    public function createInstance(Domain\Application $application, $settings = null)
    {
        $applicationEntity = $this->resolveApplicationEntity($application);
        if (empty($applicationEntity)) {
            throw new \RuntimeException('Could not find an application entity');
        }

        $instanceEntity = new Entity\AppStore\AppInstance();
        $instanceEntity->setApp($applicationEntity);

        $this->mapApplicationToInstance($application, $instanceEntity);
        if (! is_null($settings)) {
            $instanceEntity->setSettings($settings);
        }

        $this->persistInstance($instanceEntity);
        return $instanceEntity;
    }

    public function deleteInstance(Domain\ApplicationInstance $instance) {

        $applicationInstanceEntity = $this->resolveApplicationInstance($instance);
        $isSingleInstance = $this->getIsSingleInstanceProperty($applicationInstanceEntity);

        if ($isSingleInstance) {
            $application = $applicationInstanceEntity->getApp();
            $this->deleteApplication($application);
            return $instance;
        }

        $deleteQueries = [];
        // create delete instance state by id
        $deleteQueries[] = $this->entityManager->createQueryBuilder()
            ->delete(Entity\AppStore\AppState::class, 's')
            ->where('s.appInstanceId = :appInstanceId')
            ->setParameter('applicationId', $instance->getId())
            ->getQuery()
        ;
        // create delete instance by id
        $deleteQueries[] = $this->entityManager->createQueryBuilder()
            ->delete(Entity\AppStore\AppInstance::class, 'a')
            ->where('a.id = :appInstanceId')
            ->setParameter('applicationId', $instance->getId())
            ->getQuery()
        ;

        $this->executeTransaction($deleteQueries);
        return $instance;
    }

    /**
     * @param $queries ORM\Query[]
     * @throws \Exception
     */
    private function executeTransaction($queries)
    {
        $connection = $this->entityManager->getConnection();
        $connection->beginTransaction();
        try {
            foreach ($queries as $query) {
                $query->execute();
            }
            $connection->commit();
        } catch (\Exception $e) {
            $connection->rollback();
            throw $e;
        }
    }

    public function deleteApplication(Domain\Application $application)
    {
        $applicationEntity = $this->resolveApplicationEntity($application);
        $instanceFinder = new ApplicationInstanceDoctrineFinder($this->entityManager);
        $applicationInstances = $instanceFinder->findByApplication($applicationEntity->getName());

        $applicationInstanceIds = array_map(
            function (Entity\AppStore\AppInstance $instance) { return $instance->getId(); }
            , $applicationInstances
        );

        $deleteQueries = [];
        // create delete instance state by id
        $deleteQueries[] = $this->entityManager->createQueryBuilder()
            ->delete(Entity\AppStore\AppState::class, 's')
            ->where('s.appInstanceId IN (:idList)')->setParameter('idList', $applicationInstanceIds)
            ->getQuery()
        ;
        // create delete instance by id
        $deleteQueries[] = $this->entityManager->createQueryBuilder()
            ->delete(Entity\AppStore\AppInstance::class, 'a')
            ->where('a.id IN (:idList)')->setParameter('idList', $applicationInstanceIds)
            ->getQuery()
        ;
        // create delete assets by id
        $deleteQueries[] = $this->entityManager->createQueryBuilder()
            ->delete(Entity\AppStore\AppAsset::class, 'a')
            ->where('a.appId = :appId')->setParameter('appId', $application->getId())
            ->getQuery()
        ;
        // create delete app by id
        $deleteQueries[] = $this->entityManager->createQueryBuilder()
            ->delete(Entity\AppStore\App::class, 'a')
            ->where('a.id = :id')->setParameter('id', $application->getId())
            ->getQuery()
        ;

        $this->executeTransaction($deleteQueries);
        return $application;
    }

    private function getIsSingleInstanceProperty(Entity\AppStore\AppInstance $appInstance)
    {
        $qb = $this->entityManager->createQueryBuilder();
        $qb
            ->from(Entity\AppStore\AppInstance::class, 'i')
            ->select('COUNT(i.id)')
            ->innerJoin('i.app', 'a')
            ->where('a.id = :applicationId')
            ->setMaxResults(2)
            ->setParameter('applicationId', $appInstance->getApplicationId())
        ;

        $applicationInstancesCount = $qb->getQuery()->getSingleScalarResult();
        return intval($applicationInstancesCount, 10) === 1;
    }

    /**
     * @param Domain\ApplicationInstance $instance
     * @return Entity\AppStore\AppInstance
     */
    private function resolveApplicationInstance(Domain\ApplicationInstance $instance)
    {
        if ($instance instanceof Entity\AppStore\AppInstance) {
            return $instance;
        }

        $entityId = $instance->getId();
        $entity = $this->entityManager->getRepository(Entity\AppStore\AppInstance::class)->find($entityId);
        return $entity;
    }

    /**
     * Retrieves the corresponding persistence entity for the given application domain entity
     *
     * @param Domain\Application $application
     * @return Entity\AppStore\App
     */
    private function resolveApplicationEntity(Domain\Application $application)
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

    private function mapApplicationToInstance(Domain\Application $app, Entity\AppStore\AppInstance $instance)
    {
        //TODO: inject a manifest reader and properly handle this operation
        $manifestReader = new Infrastructure\AppManifestJsonReader();
        $manifestObject = $manifestReader->readManifest($app->getManifest());

        $settings = $manifestObject->getDefaultSettings();
        $instance->setSettings( json_encode($settings) );
        $instance->setScope( $manifestObject->getScope() );
    }

    /**
     * @param Entity\AppStore\AppInstance $instance
     */
    private function persistInstance(Entity\AppStore\AppInstance $instance)
    {
        $this->entityManager->persist($instance);
        $this->entityManager->flush();
    }
}



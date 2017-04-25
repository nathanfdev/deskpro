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
use Application\DeskPRO;
use DeskPRO\Bundle\AppBundle\Entity;
use DeskPRO\Bundle\AppStoreBundle\Domain;
use DeskPRO\Bundle\AppStoreBundle\Infrastructure;
use Doctrine\ORM;
use Orb\Data\ContentTypes;

class ApplicationManagerService implements Domain\ApplicationManager
{
    /** @var ORM\EntityManager */
    private $entityManager;

    /** @var DeskproBlobStorage */
    private $blobStorage;

    /**
     * @var EntityIdentityMapResolver
     */
    private $entityResolver;

    /**
     * ApplicationService constructor.
     * @param ORM\EntityManager $entityManager
     * @param DeskproBlobStorage $blobStorage
     * @param EntityIdentityMapResolver $entityResolver
     */
    public function __construct(ORM\EntityManager $entityManager, DeskproBlobStorage $blobStorage, EntityIdentityMapResolver $entityResolver) {
        $this->entityManager = $entityManager;
        $this->blobStorage = $blobStorage;
        $this->entityResolver = $entityResolver;
    }


    /**
     * @param Domain\AppBundle $bundle
     * @return Domain\ApplicationInstance
     */
    public function createFirstInstance(Domain\AppBundle $bundle)
    {
        //save app
        $appEntity = $this->createAppEntity($bundle);

        //save blob assets
        $blobs = $this->createBlobEntityList($bundle);
        $this->createAssetBlobEntityList($bundle, $blobs, $appEntity);

        $instanceEntity = $this->createInstance($appEntity);
        return $instanceEntity;
    }

    /**
     * @param Domain\AppBundle $bundle
     * @return Entity\AppStore\App
     */
    private function createAppEntity(Domain\AppBundle $bundle)
    {
        $entities = [];
        $entityOperation = function (ORM\EntityManager $entityManager, $entity) { $entityManager->persist($entity); };

        //save app and instance

        $appEntity = $this->mapManifestStringToApp($bundle->getManifestAsString(), new Entity\AppStore\App());
        $entities[] = $appEntity;
        $this->executeEntityOperationTransaction($entities, $entityOperation);

        return $appEntity;
    }

    /**
     * @param Domain\AppBundle $bundle
     * @return DeskPRO\Entity\Blob[]
     */
    private function createBlobEntityList(Domain\AppBundle $bundle)
    {
        $blobs = [];
        foreach ($bundle->listAllResources() as $resource) {
            $fileExtension = $resource->getFileExtension();
            $contentType = ContentTypes::getContentTypeFromExtension($fileExtension);
            if (empty($contentType)) {
                // could be a problem for js.map files which get ther file extension as map instead of js.map,
                // but see http://stackoverflow.com/questions/19911929/what-mime-type-should-i-use-for-javascript-source-map-files
                $contentType = 'application/octet-stream';
            }
            $blob = $this->blobStorage->createBlobRecordFromString($resource->getContent(), $resource->getPath(), $contentType);
            $blobs[] = $blob;
        }

        return $blobs;
    }

    /**
     * @param Domain\AppBundle $bundle
     * @param DeskPRO\Entity\Blob[] $blobs
     * @param Entity\AppStore\App $app
     */
    private function createAssetBlobEntityList(Domain\AppBundle $bundle, $blobs, $app) {
        $entities = [];
        foreach ($bundle->listAllResources() as $resource) {
            $asset = new Entity\AppStore\AppAssetBlob();
            $asset->setApp($app);

            $path = $resource->getPath();
            $asset->setPath( $path );

            $blob = current($blobs);
            next($blobs);
            $asset->setBlob($blob);

            $entities[] = $asset;
        }

        $entityOperation = function (ORM\EntityManager $entityManager, $entity) { $entityManager->persist($entity); };
        $this->executeEntityOperationTransaction($entities, $entityOperation);
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
        $applicationEntity = $this->entityResolver->resolveApplicationEntity($application);
        if (empty($applicationEntity)) {
            throw new \RuntimeException('Could not find an application entity');
        }

        $instanceEntity = new Entity\AppStore\AppInstance();
        $instanceEntity->setApp($applicationEntity);

        $this->mapApplicationToInstance($application, $instanceEntity);
        if (! is_null($settings)) {
            $instanceEntity->setSettings($settings);
        }

        $entityOperation = function (ORM\EntityManager $entityManager, $entity) { $entityManager->persist($entity); };
        $this->executeEntityOperationTransaction([$instanceEntity], $entityOperation);

        return $instanceEntity;
    }

    public function deleteInstance(Domain\ApplicationInstance $instance) {

        $applicationInstanceEntity = $this->entityResolver->resolveApplicationInstance($instance);
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

        $this->executeQueryTransaction($deleteQueries);
        return $instance;
    }

    public function deleteApplication(Domain\Application $application)
    {
        $applicationEntity = $this->entityResolver->resolveApplicationEntity($application);

        $assetFinder = new AssetDoctrineFinder($this->entityManager);
        $assetList = $assetFinder->findAllApplicationAssets($application);
        $this->deleteAssetBlobList($applicationEntity, $assetList);

        $this->deleteAssetList($applicationEntity);

        $instanceFinder = new ApplicationInstanceDoctrineFinder($this->entityManager);
        $instanceList = $instanceFinder->findByApplication($applicationEntity->getName());
        $this->deleteInstanceList($applicationEntity, $instanceList);

        // create delete app by id
        $deleteQueries = [];
        $deleteQueries[] = $this->entityManager->createQueryBuilder()
            ->delete(Entity\AppStore\App::class, 'a')
            ->where('a.id = :id')->setParameter('id', $application->getId())
            ->getQuery()
        ;

        $this->executeQueryTransaction($deleteQueries);
        return $application;
    }

    /**
     * @param Entity\AppStore\App $app
     * @param Entity\AppStore\AppAssetBlob[] $assetList
     */
    private function deleteAssetBlobList(Entity\AppStore\App $app, $assetList)
    {
        /** @var DeskPRO\Entity\Blob $blobs */
        $blobs = array_map(
            function (Entity\AppStore\AppAssetBlob $asset) { return $asset->getBlob(); }
            , $assetList
        );
        $entityOperation = function (ORM\EntityManager $entityManager, $entity) { $entityManager->remove($entity); };
        $this->executeEntityOperationTransaction($assetList, $entityOperation);

        foreach ($blobs as $blob) {
            $this->blobStorage->deleteBlobRecord($blob, false);
        }
    }

    /**
     * @param Entity\AppStore\App $app
     */
    private function deleteAssetList(Entity\AppStore\App $app)
    {
        $deleteQueries = [];
        // create delete assets by id
        $deleteQueries[] = $this->entityManager->createQueryBuilder()
            ->delete(Entity\AppStore\AppAsset::class, 'a')
            ->where('a.appId = :appId')->setParameter('appId', $app->getId())
            ->getQuery()
        ;

        $this->executeQueryTransaction($deleteQueries);
    }

    /**
     * @param Entity\AppStore\App $app
     * @param Entity\AppStore\AppInstance[] $instanceList
     */
    private function deleteInstanceList(Entity\AppStore\App $app, $instanceList)
    {
        $instanceIdList = array_map(
            function (Entity\AppStore\AppInstance $instance) { return $instance->getId(); }
            , $instanceList
        );

        $deleteQueries = [];
        // create delete instance state by id (this can cause table locks because we're using a non-unique index in where)
        $deleteQueries[] = $this->entityManager->createQueryBuilder()
            ->delete(Entity\AppStore\AppState::class, 's')
            ->where('s.appInstanceId IN (:idList)')->setParameter('idList', $instanceIdList)
            ->getQuery()
        ;
        // create delete instance by id
        $deleteQueries[] = $this->entityManager->createQueryBuilder()
            ->delete(Entity\AppStore\AppInstance::class, 'a')
            ->where('a.id IN (:idList)')->setParameter('idList', $instanceIdList)
            ->getQuery()
        ;

        $this->executeQueryTransaction($deleteQueries);
    }

    /**
     * @param Entity\AppStore\AppInstance $appInstance
     * @return bool
     */
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
     * @param Domain\Application $app
     * @param Entity\AppStore\AppInstance $instance
     */
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
     * @param array $entities
     * @param \Closure $entityOperation
     * @throws \Exception
     */
    private function executeEntityOperationTransaction($entities, $entityOperation) {
        $connection = $this->entityManager->getConnection();
        $connection->beginTransaction();
        try {
            foreach ($entities as $entity) {
                $entityOperation($this->entityManager, $entity);
            }

            $this->entityManager->flush();
            $connection->commit();
        } catch (\Exception $e) {
            $connection->rollback();
            throw $e;
        }
    }

    /**
     * @param $queries ORM\Query[]
     * @throws \Exception
     */
    private function executeQueryTransaction($queries)
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

}



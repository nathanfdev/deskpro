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
use DeskPRO\Bundle\AppStoreBundle\Domain;
use DeskPRO\Bundle\AppStoreBundle\Domain\AppBundle;
use DeskPRO\Bundle\AppStoreBundle\Domain\Application;
use DeskPRO\Bundle\AppStoreBundle\Domain\ApplicationInstance;
use DeskPRO\Bundle\AppStoreBundle\Domain\ApplicationInstanceCreator;
use DeskPRO\Bundle\AppStoreBundle\Infrastructure;
use JsonSchema;
use Doctrine\ORM;

class ApplicationInstanceService implements ApplicationInstanceCreator
{
    /** @var ORM\EntityManager */
    private $entityManager;

    /**
     * ApplicationService constructor.
     * @param ORM\EntityManager $entityManager
     */
    public function __construct(
        ORM\EntityManager $entityManager
    ) {
        $this->entityManager = $entityManager;
    }

    /**
     * @param AppBundle $bundle
     * @return ApplicationInstance
     */
    public function createFirstInstance(AppBundle $bundle)
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
     * @param Application $application
     * @param string|null $settings
     * @return  Entity\AppStore\AppInstance
     */
    public function createInstance(Application $application, $settings = null)
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

    /**
     * Retrieves the corresponding persistence entity for the given application domain entity
     *
     * @param Application $application
     * @return Entity\AppStore\App
     */
    private function resolveApplicationEntity(Application $application)
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

    private function mapApplicationToInstance(Application $app, Entity\AppStore\AppInstance $instance)
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



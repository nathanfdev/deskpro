<?php

namespace DeskPRO\Bundle\AppStoreBundle\Infrastructure;

use DeskPRO\Bundle\AppBundle\HttpKernel\Config\FileLocator;
use Doctrine\ORM;
use JsonSchema\Validator;

class Services
{
    /**
     * @param ORM\EntityManager $entityManager
     * @return AppStorage\AccessService
     */
    public static function createAppStorageAccessService(ORM\EntityManager $entityManager)
    {
        $service = new AppStorage\AccessService($entityManager, new EntityQueryBuilders());
        return $service;
    }

    /**
     * @param ORM\EntityManager $entityManager
     *
     * @return ApplicationInstanceDoctrineFinder
     */
    public static function createApplicationInstanceFinder(ORM\EntityManager $entityManager)
    {
        return new ApplicationInstanceDoctrineFinder($entityManager);
    }

    /**
     * @param FileLocator $schemaLocator
     * @param string $currentManifestVersion
     *
     * @return AppBundleValidator
     */
    public static function createAppBundleValidator(FileLocator $schemaLocator, $currentManifestVersion)
    {
        $schemaDir = '@AppStoreBundle/Resources/manifest';
        $schemaLocator = new ManifestSchemaLocator($schemaLocator, $schemaDir, $currentManifestVersion);

        $service = new AppBundleValidator(new Validator(), $schemaLocator);

        return $service;
    }

    /**
     * @param ORM\EntityManager $entityManager
     *
     * @return AssetDoctrineFinder
     */
    public static function createApplicationAssetService(ORM\EntityManager $entityManager)
    {
        return new AssetDoctrineFinder($entityManager);
    }

    /**
     * @param ORM\EntityManager $entityManager
     *
     * @return ApplicationDoctrineFinder
     */
    public static function createApplicationFinder(ORM\EntityManager $entityManager)
    {
        return new ApplicationDoctrineFinder($entityManager);
    }

    /**
     * @param ORM\EntityManager $entityManager
     *
     * @return AppStorage\StateEntityFinder
     */
    public static function createApplicationStateFinder(ORM\EntityManager $entityManager)
    {
        return new AppStorage\StateEntityFinder($entityManager);
    }
}

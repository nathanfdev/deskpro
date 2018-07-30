<?php

namespace DeskPRO\Bundle\AppStoreBundle\ParamConverter;

use DeskPRO\Bundle\AppStoreBundle\Infrastructure;
use DeskPRO\Bundle\AppStoreBundle\Domain;
use DeskPRO\Bundle\AppStoreBundle\Infrastructure\AppBundleAdapters\BundleFileHandlingStrategyZip;
use Doctrine\ORM;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;

/**
 * Factory for param converters
 */
class ParamConverters
{
    static public function createBundleConverterZip(ContainerInterface $container)
    {
        $fileConverter = RequestBodyToTemporaryFileConverter::createFromGlobals();
        /** @var BundleFileHandlingStrategyZip $bundleReader */
        $bundleReader = $container->get(BundleFileHandlingStrategyZip::class);
        return new BundleFileReaderAdapter($bundleReader, $fileConverter);
    }

    /**
     * @param ORM\EntityManager $entityManager
     * @return OauthProviderConnectionLoaderConverter
     */
    static public function createOauthProviderConnectionLoader(ORM\EntityManager $entityManager)
    {
        return new OauthProviderConnectionLoaderConverter($entityManager);
    }

    /**
     * @param ORM\EntityManager $entityManager
     * @return AppInstanceParamConverter
     */
    static public function createAppInstanceConverter(ORM\EntityManager $entityManager)
    {
        $finder = new Infrastructure\ApplicationInstanceDoctrineFinder($entityManager);
        return new AppInstanceParamConverter($finder, new Infrastructure\IdentifierParser());
    }

    /**
     * @param ORM\EntityManager $entityManager
     * @return AppParamConverter
     */
    static public function createAppConverter(ORM\EntityManager $entityManager)
    {
        $finder = new Infrastructure\ApplicationDoctrineFinder($entityManager);
        return new AppParamConverter($finder, new Infrastructure\IdentifierParser());
    }

    /**
     * @return AssetFilterParamConverter
     */
    static public function createAssetFilterConverter()
    {
        $filterConverter = new Domain\SearchFilters();
        return new AssetFilterParamConverter($filterConverter);
    }

}

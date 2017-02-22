<?php

namespace DeskPRO\Bundle\ApiBundle\Apps;

use DeskPRO\Bundle\AppStoreBundle\Infrastructure;
use DeskPRO\Bundle\AppStoreBundle\Domain;
use Doctrine\ORM;

/**
 * Factory for param converters
 */
class ParamConverters
{
    /**
     * @param ORM\EntityManager $entityManager
     * @return AppInstanceParamConverter
     */
    public static function createAppInstanceConverter(ORM\EntityManager $entityManager)
    {
        $finder = new Infrastructure\DoctrineApplicationInstanceFinder($entityManager);
        return new AppInstanceParamConverter($finder, new IdentifierParser());
    }

    /**
     * @param ORM\EntityManager $entityManager
     * @return AppParamConverter
     */
    public static function createAppConverter(ORM\EntityManager $entityManager)
    {
        $finder = new Infrastructure\DoctrineApplicationFinder($entityManager);
        return new AppParamConverter($finder, new IdentifierParser());
    }

    /**
     * @param ORM\EntityManager $entityManager
     * @return AppStateParamConverter
     */
    public static function createAppStateConverter(ORM\EntityManager $entityManager)
    {
        return new AppStateParamConverter($entityManager, new IdentifierParser());
    }

    /**
     * @return AssetFilterParamConverter
     */
    public static function createAssetFilterConverter()
    {
        $filterConverter = new Domain\SearchFilters();
        return new AssetFilterParamConverter($filterConverter);
    }

    /**
     * @return StateFilterParamConverter
     */
    public static function createStateFilterConverter()
    {
        $filterConverter = new Domain\SearchFilters();
        return new StateFilterParamConverter($filterConverter);
    }


}

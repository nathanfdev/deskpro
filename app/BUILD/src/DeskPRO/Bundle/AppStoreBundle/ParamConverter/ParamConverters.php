<?php

namespace DeskPRO\Bundle\AppStoreBundle\ParamConverter;

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
        $finder = new Infrastructure\ApplicationInstanceDoctrineFinder($entityManager);
        return new AppInstanceParamConverter($finder, new Infrastructure\IdentifierParser());
    }

    /**
     * @param ORM\EntityManager $entityManager
     * @return AppParamConverter
     */
    public static function createAppConverter(ORM\EntityManager $entityManager)
    {
        $finder = new Infrastructure\ApplicationDoctrineFinder($entityManager);
        return new AppParamConverter($finder, new Infrastructure\IdentifierParser());
    }

    /**
     * @param ORM\EntityManager $entityManager
     * @return AppStateParamConverter
     */
    public static function createAppStateConverter(ORM\EntityManager $entityManager)
    {
        $finder = new Infrastructure\ApplicationStateDoctrineFinder($entityManager);
        return new AppStateParamConverter($finder, new Infrastructure\IdentifierParser());
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

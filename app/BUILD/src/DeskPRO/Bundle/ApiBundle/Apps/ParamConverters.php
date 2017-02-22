<?php

namespace DeskPRO\Bundle\ApiBundle\Apps;

use DeskPRO\Bundle\AppStoreBundle\Infrastructure;
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
        return new AppInstanceParamConverter($entityManager, new IdentifierParser());
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
     * @param ORM\EntityManager $entityManager
     * @return AssetFilterParamConverter
     */
    public static function createAssetFilterConverter(ORM\EntityManager $entityManager)
    {
        return new AssetFilterParamConverter($entityManager);
    }

    /**
     * @param ORM\EntityManager $entityManager
     * @return StateFilterParamConverter
     */
    public static function createStateFilterConverter(ORM\EntityManager $entityManager)
    {
        return new StateFilterParamConverter($entityManager);
    }


}

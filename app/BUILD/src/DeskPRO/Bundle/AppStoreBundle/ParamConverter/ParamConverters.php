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

namespace DeskPRO\Bundle\AppStoreBundle\ParamConverter;

use DeskPRO\Bundle\AppStoreBundle\Domain;
use DeskPRO\Bundle\AppStoreBundle\Infrastructure;
use Doctrine\ORM;

/**
 * Factory for param converters.
 */
class ParamConverters
{
    /**
     * @param ORM\EntityManager $entityManager
     *
     * @return OauthProviderConnectionLoaderConverter
     */
    public static function createOauthProviderConnectionLoader(ORM\EntityManager $entityManager)
    {
        return new OauthProviderConnectionLoaderConverter($entityManager);
    }

    /**
     * @param ORM\EntityManager $entityManager
     *
     * @return AppInstanceParamConverter
     */
    public static function createAppInstanceConverter(ORM\EntityManager $entityManager)
    {
        $finder = new Infrastructure\ApplicationInstanceDoctrineFinder($entityManager);

        return new AppInstanceParamConverter($finder, new Infrastructure\IdentifierParser());
    }

    /**
     * @param ORM\EntityManager $entityManager
     *
     * @return AppParamConverter
     */
    public static function createAppConverter(ORM\EntityManager $entityManager)
    {
        $finder = new Infrastructure\ApplicationDoctrineFinder($entityManager);

        return new AppParamConverter($finder, new Infrastructure\IdentifierParser());
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

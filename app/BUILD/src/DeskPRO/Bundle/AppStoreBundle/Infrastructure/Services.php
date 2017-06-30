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

use DeskPRO\Bundle\AppBundle\HttpKernel\Config\FileLocator;
use Doctrine\ORM;

class Services
{
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
     *
     * @return AppBundleValidator
     */
    public static function createAppBundleValidator(FileLocator $schemaLocator)
    {
        $schemaPath = $schemaLocator->locate('@AppStoreBundle/Resources/manifest/schema.default.json');
        $schemaInfo = new \SplFileInfo($schemaPath);

        $service = new AppBundleValidator($schemaInfo);

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
     * @return ApplicationStateDoctrineFinder
     */
    public static function createApplicationStateFinder(ORM\EntityManager $entityManager)
    {
        return new ApplicationStateDoctrineFinder($entityManager);
    }
}

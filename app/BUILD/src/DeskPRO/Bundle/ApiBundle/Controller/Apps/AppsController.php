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

namespace DeskPRO\Bundle\ApiBundle\Controller\Apps;

use DeskPRO\Bundle\AppStoreBundle;
use FOS\RestBundle\Controller\FOSRestController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

use DeskPRO\Bundle\AppBundle\Entity;
use FOS\RestBundle\Controller\Annotations as Rest;
use Symfony\Component\HttpFoundation;
use Symfony\Component\HttpKernel\Exception\ServiceUnavailableHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation as DeskproAnnotations;

/**
 * Class AppsController
 *
 * @DeskproAnnotations\ApiModes("standard")
 * @Rest\Route("/apps")
 */
class AppsController extends FOSRestController
{
    /**
     * @Rest\Get("")
     *
     * @param HttpFoundation\Request $request
     * @return string
     */
    public function listApplicationAction(HttpFoundation\Request $request)
    {
        //convert query params into a filter
        $searchFilter = new AppStoreBundle\Domain\SearchApplicationInstanceFilter();
        $parameterBag = $request->attributes;
        if ($parameterBag->has('scope')) {
            $searchFilter->setScope( $parameterBag->get('scope') );
        }

        /** @var AppStoreBundle\Domain\ApplicationInstanceFinder $instanceFinder */
        $instanceFinder = $this->container->get(AppStoreBundle\Domain\ApplicationInstanceFinder::class);
        if ($searchFilter->isEmpty()) { //
            $instances = $instanceFinder->findAll();
        } else {
            $instances = $instanceFinder->findByFilter($searchFilter);
        }

        if (empty($instances)) {
            return [];
        }

        //retrieve the application manifests for the instances we found
        /** @var AppStoreBundle\Domain\ApplicationFinder $applicationFinder */
        $applicationFinder = $this->container->get(AppStoreBundle\Domain\ApplicationFinder::class);
        $applications = $applicationFinder->findAllById(
            array_map(
                function(AppStoreBundle\Domain\ApplicationInstance $instance) { return $instance->getApplicationId(); }
                , $instances
            )
        );
        $manifestMap = array_reduce(
            $applications
            , function (array $carry, AppStoreBundle\Domain\Application $app) { $carry[$app->getId()] = json_decode($app->getManifest(), $assoc = true); return $carry; }
            , []
        );

        $converter = function(AppStoreBundle\Domain\ApplicationInstance $instance) use ($manifestMap) {
            return [
                'id' => $instance->getId(),
                'application_id' => $instance->getApplicationId(),
                'settings' => json_decode($instance->getSettings(), $assoc = true),
                'targets' => $manifestMap[$instance->getApplicationId()]['targets']
            ];
        };
        return array_map($converter, $instances);
    }


    /**
     * @Rest\Get("/{application}")
     *
     * @param Entity\AppStore\AppInstance $application
     * @ParamConverter("application", class="AppBundle:Entity\AppStore\AppInstance", converter="DeskPRO\Bundle\AppStoreBundle\ParamConverter\AppInstanceParamConverter")
     * @return string
     */
    public function getApplicationAction(Entity\AppStore\AppInstance $application)
    {
        return $application;
    }

    /**
     * @Rest\Post("", condition="request.headers.get('Content-Type') matches '#application/zip#i'")
     * @ParamConverter("bundle", class="AppStoreBundle:Infrastructure\AppZipArchiveBundle", converter="DeskPRO\Bundle\AppStoreBundle\ParamConverter\AppZipArchiveBundleParamConverter")
     * @param AppStoreBundle\Infrastructure\AppZipArchiveBundle $bundle
     * @return string
     */
    public function createFromZipFileAction(AppStoreBundle\Infrastructure\AppZipArchiveBundle $bundle)
    {
        /** @var AppStoreBundle\Domain\AppBundleValidator $bundleValidator */
        $bundleValidator = $this->container->get(AppStoreBundle\Domain\AppBundleValidator::class);
        $validBundle = $bundleValidator->validateBundle($bundle);

        if (! $validBundle) { //TODO provide a more elaborate exception body
            throw new UnprocessableEntityHttpException('invalid bundle');
        }

        /** @var AppStoreBundle\Domain\ApplicationInstanceCreator $instanceCreator */
        $instanceCreator = $this->container->get(AppStoreBundle\Domain\ApplicationInstanceCreator::class);
        $instance = $instanceCreator->createFirstInstance($bundle);

        return $instance;
    }

    /**
     * @Rest\Post("/{application}", condition="request.headers.get('Content-Type') matches '#application/zip#i'")
     * @ParamConverter("application", class="AppBundle:Entity\AppStore\AppInstance", converter="DeskPRO\Bundle\AppStoreBundle\ParamConverter\AppInstanceParamConverter")
     * @ParamConverter("bundle", class="AppStoreBundle:Infrastructure\AppZipArchiveBundle", converter="DeskPRO\Bundle\AppStoreBundle\ParamConverter\AppZipArchiveBundleParamConverter")
     * @param Entity\AppStore\AppInstance $application
     * @param AppStoreBundle\Infrastructure\AppZipArchiveBundle $bundle
     * @return string
     */
    public function updateFromZipFileAction(Entity\AppStore\AppInstance $application, AppStoreBundle\Infrastructure\AppZipArchiveBundle $bundle)
    {
        throw new ServiceUnavailableHttpException('endpoint not available');
    }

    /**
     * @Rest\POST("", condition="request.headers.get('Content-Type') matches '#application/json#i'")
     * @ParamConverter("file", class="SplFileInfo", converter="DeskPRO\Bundle\ApiBundle\ParamConverter\RequestBodyToTemporaryFileConverter")
     * @return string
     */
    public function createFromUrlAction()
    {
        throw new ServiceUnavailableHttpException('endpoint not available');
    }

    /**
     * @Rest\Post("/{application}", condition="request.headers.get('Content-Type') matches '#application/json#i'")
     * @ParamConverter("application", class="AppBundle:Entity\AppStore\App", converter="DeskPRO\Bundle\AppStoreBundle\ParamConverter\AppParamConverter")
     * @param Entity\AppStore\App $application
     */
    public function updateAppFromUrlAction(Entity\AppStore\App $application)
    {
        throw new ServiceUnavailableHttpException('endpoint not available');
    }

    /**
     * @Rest\Delete("/{application}")
     *
     * @param Entity\AppStore\AppInstance $instance
     * @ParamConverter("application", class="AppBundle:Entity\AppStore\AppInstance", converter="DeskPRO\Bundle\AppStoreBundle\ParamConverter\AppInstanceParamConverter")
     */
    public function deleteApplicationAction(Entity\AppStore\AppInstance $instance)
    {
        throw new ServiceUnavailableHttpException('endpoint not available');
    }

    /**
     * @Rest\Get("/{application}/manifest")
     *
     * @ParamConverter("application", class="AppBundle:Entity\AppStore\App", converter="DeskPRO\Bundle\AppStoreBundle\ParamConverter\AppParamConverter")
     * @param Entity\AppStore\App $application
     *
     * @return array
     */
    public function getManifestAction(Entity\AppStore\App $application)
    {
        //TODO getManifest should return an object
        $manifestString = $application->getManifest();
        $manifestArray = json_decode($manifestString, true);

        return $manifestArray;
    }

    /**
     * @Rest\Get("/{application}/settings")
     *
     * @ParamConverter("application", class="AppBundle:Entity\AppStore\AppInstance", converter="DeskPRO\Bundle\AppStoreBundle\ParamConverter\AppInstanceParamConverter")
     * @param Entity\AppStore\AppInstance $application
     *
     * @return array
     */
    public function getSettingsAction(Entity\AppStore\AppInstance $application)
    {
        //TODO getSettings should return an object
        $settingsString = $application->getSettings();
        $settingsArray = json_decode($settingsString, true);

        return $settingsArray;
    }

    /**
     * @Rest\Get("/{application}/assets")
     *
     * @ParamConverter("application", class="AppBundle:Entity\AppStore\App", converter="DeskPRO\Bundle\AppStoreBundle\ParamConverter\AppParamConverter")
     * @ParamConverter("searchFilter", class="AppStoreBundle:Domain\AssetFilter", converter="DeskPRO\Bundle\AppStoreBundle\ParamConverter\AssetFilterParamConverter")
     *
     * @param Entity\AppStore\App $application
     * @param AppStoreBundle\Domain\SearchAssetFilter $searchFilter
     */
    public function listAssetsAction(Entity\AppStore\App $application, AppStoreBundle\Domain\SearchAssetFilter $searchFilter)
    {
        /** @var AppStoreBundle\Domain\AssetFinder $assetFinder */
        $assetFinder = $this->container->get(AppStoreBundle\Domain\AssetFinder::class);
        $assets = $assetFinder->findApplicationAssets($application, $searchFilter);

        return $assets;
    }
}

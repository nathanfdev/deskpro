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
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use DeskPRO\Bundle\AppBundle\Entity;
use FOS\RestBundle\Controller\Annotations as Rest;
use Symfony\Component\HttpKernel\Exception\ServiceUnavailableHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

/**
 * Class AppsController
 *
 * @ApiModes("all")
 * @Rest\Route("/apps")
 */
class AppsController extends FOSRestController {

    /**
     * @Rest\GET("/{application}")
     *
     * @param Entity\AppStore\AppInstance $application
     * @ParamConverter("application", class="AppBundle:Entity\AppStore\AppInstance", converter="DeskPRO\Bundle\ApiBundle\Apps\AppInstanceParamConverter")
     * @return string
     */
    public function getApplication(Entity\AppStore\AppInstance $application)
    {
        return $application;
    }

    /**
     * @Rest\POST("", condition="request.headers.get('Content-Type') matches '#application/zip#i'")
     * @ParamConverter("bundle", class="AppStoreBundle:Infrastructure\AppZipArchiveBundle", converter="DeskPRO\Bundle\ApiBundle\Apps\AppZipArchiveBundleParamConverter")
     * @param AppStoreBundle\Infrastructure\AppZipArchiveBundle $bundle
     * @return string
     */
    public function createFromZipFile(AppStoreBundle\Infrastructure\AppZipArchiveBundle $bundle)
    {
        /** @var AppStoreBundle\Domain\ApplicationCreator $applicationCreator */
        $applicationCreator = $this->container->get(AppStoreBundle\Domain\ApplicationCreator::class);
        $validBundle = $applicationCreator->verifyBundle($bundle);

        if (! $validBundle) { //TODO provide a more elaborate exception body
            throw new UnprocessableEntityHttpException('invalid bundle');
        }
        $application = $applicationCreator->createApplication($bundle);

        /** @var AppStoreBundle\Domain\ApplicationInstanceCreator $instanceCreator */
        $instanceCreator = $this->container->get(AppStoreBundle\Domain\ApplicationInstanceCreator::class);
        $instance = $instanceCreator->createInstance($application);

        return $instance;
    }

    /**
     * @Rest\POST("/{application}", condition="request.headers.get('Content-Type') matches '#application/zip#i'")
     * @ParamConverter("application", class="AppBundle:Entity\AppStore\App", converter="DeskPRO\Bundle\ApiBundle\Apps\AppParamConverter")
     * @ParamConverter("bundle", class="AppStoreBundle:Infrastructure\AppZipArchiveBundle", converter="DeskPRO\Bundle\ApiBundle\Apps\AppZipArchiveBundleParamConverter")
     * @param Entity\AppStore\App $application
     * @param AppStoreBundle\Infrastructure\AppZipArchiveBundle $bundle
     * @return string
     */
    public function updateFromZipFile(Entity\AppStore\App $application, AppStoreBundle\Infrastructure\AppZipArchiveBundle $bundle)
    {
        return $application;
    }

    /**
     * @Rest\POST("", condition="request.headers.get('Content-Type') matches '#application/json#i'")
     * @ParamConverter("file", class="SplFileInfo", converter="DeskPRO\Bundle\ApiBundle\ParamConverter\RequestBodyToTemporaryFileConverter")
     * @return string
     */
    public function createFromUrl()
    {
        throw new ServiceUnavailableHttpException('endpoint not available');
    }

    /**
     * @Rest\POST("/{application}", condition="request.headers.get('Content-Type') matches '#application/json#i'")
     * @ParamConverter("application", class="AppBundle:Entity\AppStore\App", converter="DeskPRO\Bundle\ApiBundle\Apps\AppParamConverter")
     * @param Entity\AppStore\App $application
     */
    public function updateAppFromUrl(Entity\AppStore\App $application)
    {
        throw new ServiceUnavailableHttpException('endpoint not available');
    }

    /**
     * @Rest\DELETE("/{application}")
     *
     * @param Entity\AppStore\AppInstance $instance
     * @ParamConverter("application", class="AppBundle:Entity\AppStore\AppInstance", converter="DeskPRO\Bundle\ApiBundle\Apps\AppInstanceParamConverter")
     */
    public function deleteApplication(Entity\AppStore\AppInstance $instance)
    {
        throw new ServiceUnavailableHttpException('endpoint not available');
    }

    /**
     * @Rest\GET("/{application}/manifest")
     *
     * @ParamConverter("application", class="AppBundle:Entity\AppStore\App", converter="DeskPRO\Bundle\ApiBundle\Apps\AppParamConverter")
     * @param Entity\AppStore\App $application
     *
     * @return array
     */
    public function getManifest(Entity\AppStore\App $application)
    {
        $manifestString = $application->getManifest();
        $manifestArray = json_decode($manifestString, true);

        return $manifestArray;
    }

    /**
     * @Rest\GET("/{application}/settings")
     *
     * @ParamConverter("application", class="AppBundle:Entity\AppStore\AppInstance", converter="DeskPRO\Bundle\ApiBundle\Apps\AppInstanceParamConverter")
     * @param Entity\AppStore\AppInstance $application
     *
     * @return array
     */
    public function getSettings(Entity\AppStore\AppInstance $application)
    {
        $settingsString = $application->getSettings();
        $settingsArray = json_decode($settingsString, true);

        return $settingsArray;
    }

    /**
     * @Rest\GET("/{application}/assets")
     *
     * @ParamConverter("application", class="AppBundle:Entity\AppStore\App", converter="DeskPRO\Bundle\ApiBundle\Apps\AppParamConverter")
     * @ParamConverter("assetFilter", class="AppStoreBundle:Domain\AssetFilter", converter="ApiBundle:Apps\AssetFilterParamConverter")
     *
     * @param Entity\AppStore\App $application
     * @param AppStoreBundle\Domain\SearchAssetFilter $assetFilter
     */
    public function getAssets(Entity\AppStore\App $application, AppStoreBundle\Domain\SearchAssetFilter $assetFilter)
    {

    }
}

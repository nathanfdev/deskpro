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
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use DeskPRO\Bundle\AppBundle\Entity;
use FOS\RestBundle\Controller\Annotations as Rest;

/**
 * Class AppsController
 *
 * @ApiModes("all")
 * @Rest\Route("/apps")
 */
class AppsController {

    /**
     * @Rest\GET("/{app_name}")
     *
     * @param Entity\AppStore\App $application
     * @ParamConverter("application", class="AppBundle:Entity\AppStore\App", converter="DeskPRO\Bundle\ApiBundle\Apps\AppParamConverter")
     * @return string
     */
    public function getApplication(Entity\AppStore\App $application)
    {
        return $application;
    }

    /**
     * @Rest\POST("/")
     *
     */
    public function createOrUpdateApp()
    {

    }

    /**
     * @Rest\DELETE("/{app_name_or_instance_id}")
     *
     * @param Entity\AppStore\AppInstance $appStoreInstance
     * @ParamConverter("appStoreInstance", class="AppBundle:Entity\AppStore\AppInstance", converter="DeskPRO\Bundle\ApiBundle\Apps\AppInstanceParamConverter")
     */
    public function deleteApplication(Entity\AppStore\AppInstance $appStoreInstance)
    {

    }

    /**
     * @Rest\GET("/{app_name_or_instance_id}/manifest")
     *
     * @ParamConverter("application", class="AppBundle:Entity\AppStore\App", converter="DeskPRO\Bundle\ApiBundle\Apps\AppParamConverter")
     * @param Entity\AppStore\App $application
     *
     * @return string
     */
    public function getManifest(Entity\AppStore\App $application)
    {
        return $application->getManifest();
    }

    /**
     * @Rest\GET("/{app_name_or_instance_id}/assets")
     *
     * @ParamConverter("application", class="AppBundle:Entity\AppStore\App", converter="DeskPRO\Bundle\ApiBundle\Apps\AppParamConverter")
     * @ParamConverter("assetFilter", class="AppStoreBundle:Domain\AssetFilter", converter="ApiBundle:Apps\AssetFilterParamConverter")
     *
     * @param Entity\AppStore\App $application
     * @param $assetFilter
     */
    public function getAssets(Entity\AppStore\App $application, AppStoreBundle\Domain\AssetFilter $assetFilter)
    {

    }
}

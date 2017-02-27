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

use DeskPRO\Bundle\AppBundle\Entity;
use DeskPRO\Bundle\AppStoreBundle;
use FOS\RestBundle\Controller\Annotations as Rest;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;

/**
 * Class AppsController
 *
 * @ApiModes("all")
 * @Rest\Route("/apps")
 */
class AppStateController
{
    /**
     * @Rest\GET("/{app_name_or_instance_id}/state")
     * @ParamConverter("instance", class="AppBundle:Entity\AppStore\AppInstance", converter="DeskPRO\Bundle\ApiBundle\Apps\AppInstanceParamConverter")
     * @ParamConverter("assetFilter", class="AppStoreBundle:Domain\AssetFilter", converter="DeskPRO\Bundle\ApiBundle\Apps\StateFilterParamConverter")
     *
     * @param Entity\AppStore\AppInstance $instance
     * @param $stateFilter
     */
    public function getState(Entity\AppStore\AppInstance $instance, AppStoreBundle\Domain\SearchStateFilter $stateFilter)
    {

    }

    /**
     * @Rest\POST("/{app_name_or_instance_id}/state")
     *
     * @ParamConverter("state", class="AppBundle:Entity\AppStore\AppState", converter="DeskPRO\Bundle\ApiBundle\Apps\AppStateParamConverter")
     * @param Entity\AppStore\AppState $state
     */
    public function createState(Entity\AppStore\AppState $state)
    {

    }

    /**
     * @Rest\PUT("/{app_name_or_instance_id}/state")
     *
     * @ParamConverter("state", class="AppBundle:Entity\AppStore\AppState", converter="DeskPRO\Bundle\ApiBundle\Apps\AppStateParamConverter")
     * @param Entity\AppStore\AppState $state
     */
    public function updateState(Entity\AppStore\AppState $state)
    {

    }

}

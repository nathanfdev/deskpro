<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2016, DeskPRO Ltd.
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

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\ApiBundle\Controller\Helpdesk;

use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDoc;
use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiUnstable;
use DeskPRO\Bundle\ApiBundle\Controller\BaseController;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use DeskPRO\Bundle\AppBundle\Settings\SettingsManager;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class SettingsController.
 *
 * @ApiModes("all")
 */
class SettingsController extends BaseController
{
    /**
     * Get current user personal settings.
     *
     * @ApiDoc(
     *     section="Helpdesk",
     *     resourceDescription="Operations about helpdesk discovering",
     *     description="Get current user personal settings",
     *     statusCodes={
     *         200="Success"
     *     }
     * )
     * @ApiUnstable()
     * @Rest\Get("/helpdesk/agent-client/settings")
     */
    public function agentClientInfoAction()
    {
        // serializing w/o serializer because it doesn't support deep objects hierarchies
        return View::create(
            ['data' => $this->getSettingsManager()->getAgentSettings()],
            Response::HTTP_OK
        );
    }

    /**
     * @return SettingsManager
     */
    private function getSettingsManager()
    {
        return $this->get('dp.app.settings_manager');
    }
}

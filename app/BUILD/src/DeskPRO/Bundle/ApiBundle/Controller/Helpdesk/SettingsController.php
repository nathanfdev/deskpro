<?php

namespace DeskPRO\Bundle\ApiBundle\Controller\Helpdesk;

use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDoc;
use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiUnstable;
use DeskPRO\Bundle\ApiBundle\Controller\BaseController;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;

/**
 * Class SettingsController.
 *
 * @ApiModes("all")
 * @Rest\Route("/helpdesk/agent-client/settings")
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
     *     },
     *     output="DeskPRO\Bundle\AppBundle\Settings\Model\AgentSettings"
     * )
     * @ApiUnstable()
     * @Rest\Get("")
     */
    public function agentClientInfoAction()
    {
        return View::create($this->wrap($this->get('dp.app.settings_manager')->getAgentSettings()));
    }
}

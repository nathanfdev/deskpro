<?php

namespace DeskPRO\Bundle\ApiBundle\Controller\Helpdesk;

use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDoc;
use DeskPRO\Bundle\ApiBundle\Controller\BaseController;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiUserContext;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;

/**
 * Class DiscoveryController.
 *
 * @ApiModes("all")
 * @Rest\Route("/helpdesk")
 */
class DiscoveryController extends BaseController
{
    /**
     * Used by apps to detect that this is a real helpdesk.
     *
     * @ApiDoc(
     *     section="Helpdesk",
     *     resourceDescription="Operations about helpdesk discovering",
     *     description="Used by apps to detect that this is a real helpdesk",
     *     statusCodes={
     *         200="Success"
     *     },
     *     output={
     *         "class"="DeskPRO\Bundle\AppBundle\Settings\Model\DiscoverSettings"
     *     }
     * )
     * @ApiUserContext("open")
     *
     * @Rest\Get("/discover")
     */
    public function discoverAction()
    {
        return View::create($this->wrap($this->get('discover_settings_resolver')->getSettings()));
    }

    /**
     * Used by apps when they need to know general information about a helpdesk such as which features are enabled.
     *
     * @ApiDoc(
     *     section="Helpdesk",
     *     resourceDescription="Operations about helpdesk discovering",
     *     description="Used by apps when they need to know general information about a helpdesk such as which features are enabled",
     *     statusCodes={
     *         200="Success"
     *     },
     *     output={
     *         "class"="DeskPRO\Bundle\AppBundle\Settings\Model\AgentClientInfo\AgentClientInfoSettings"
     *     }
     * )
     * @Rest\Get("/agent-client/info")
     */
    public function agentClientInfoAction()
    {
        return View::create($this->wrap($this->get('agent_client_info_settings_resolver')->getSettings()));
    }
}

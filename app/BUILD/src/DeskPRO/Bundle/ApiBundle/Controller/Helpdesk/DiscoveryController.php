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
use DeskPRO\Bundle\ApiBundle\Controller\BaseController;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;

/**
 * Class DiscoveryController.
 *
 * @ApiModes("all")
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
     * @Rest\Get("/helpdesk/discover")
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
     * @Rest\Get("/helpdesk/agent-client/info")
     */
    public function agentClientInfoAction()
    {
        return View::create($this->wrap($this->get('agent_client_info_settings_resolver')->getSettings()));
    }
}

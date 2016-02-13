<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2015, DeskPRO Ltd.
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

namespace DeskPRO\Bundle\ApiBundle\Controller;

use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\Response;

/**
 * API access to agent groups.
 *
 * @ApiModes("all")
 */
class AgentGroupsController extends BaseController
{
    /**
     * Retrieve the list of custom fields available for tickets.
     *
     * @Get("/agent_groups", name="api_agent_groups")
     */
    public function cgetAction()
    {
        $service = $this->get('data.user_groups');

        return View::create(
            $this->dataSerialize($service->loadAgentGroupsEnabled()),
            Response::HTTP_OK
        );
    }

    /**
     * @Get("/agent_groups/{id}", name="api_single_agent_group")
     */
    public function getAgentGroup($id)
    {
        $service = $this->get('data.user_groups');

        return View::create(
            $this->dataSerialize($service->loadSingleAgentGroupEnabled($id)),
            Response::HTTP_OK
        );
    }
}

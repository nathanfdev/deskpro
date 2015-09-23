<?php

/**************************************************************************\
 * | DeskPRO (r) has been developed by DeskPRO Ltd. http://www.deskpro.com/   |
 * | a British company located in London, England.                            |
 * |                                                                          |
 * | All source code and content Copyright (c) 2012, DeskPRO Ltd.             |
 * |                                                                          |
 * | The license agreement under which this software is released              |
 * | can be found at http://www.deskpro.com/license                           |
 * |                                                                          |
 * | By using this software, you acknowledge having read the license          |
 * | and agree to be bound thereby.                                           |
 * |                                                                          |
 * | Please note that DeskPRO is not free software. We release the full       |
 * | source code for our software because we trust our users to pay us for    |
 * | the huge investment in time and energy that has gone into both creating  |
 * | this software and supporting our customers. By providing the source code |
 * | we preserve our customers' ability to modify, audit and learn from our   |
 * | work. We have been developing DeskPRO since 2001, please help us make it |
 * | another decade.                                                          |
 * |                                                                          |
 * | Like the work you see? Think you could make it better? We are always     |
 * | looking for great developers to join us: http://www.deskpro.com/jobs/    |
 * |                                                                          |
 * | ~ Thanks, Everyone at Team DeskPRO                                       |
 * \**************************************************************************/

/**
 * DeskPRO
 *
 * @package DeskPRO
 */

namespace DeskPRO\Bundle\ApiBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use FOS\RestBundle\View\View;
use FOS\RestBundle\Controller\Annotations\Get;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;

/**
 * Class AgentTeamsController
 */
class AgentTeamsController extends BaseController
{
    /**
     * @ApiDoc(
     *      description="Count Agents in Teams",
     *      statusCodes={
     *          200="Success",
     *          400="Bad Request"
     *      }
     * )
     * @Get("/agent_teams/counts", name="api_agent_teams_count_agents")
     */
    public function getUsersCountsAction()
    {
        /** @var \DeskPRO\Bundle\AppBundle\DataService\AgentTeams\AgentTeamsDataService $service */
        $service = $this->get('data.agent_teams');

        return View::create(
            $this->createRepresentation($service->countAgentsInTeams()),
            Response::HTTP_OK
        );
    }

    /**
     * @ApiDoc(
     *      description="Return agents from team",
     *      statusCodes={
     *          200="Success",
     *          400="Bad Request"
     *      }
     * )
     * @Get("/agent_teams/{id}/agents", name="api_agent_teams_agents")
     */
    public function getAgentsAction($id)
    {
        /** @var \DeskPRO\Bundle\AppBundle\DataService\AgentTeams\AgentTeamsDataService $service */
        $service = $this->get('data.agent_teams');

        return View::create(
            $this->dataSerialize($service->getAgentsFromTeam((int)$id)),
            Response::HTTP_OK
        );
    }
}

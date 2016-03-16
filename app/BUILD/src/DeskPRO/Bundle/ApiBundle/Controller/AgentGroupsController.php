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
namespace DeskPRO\Bundle\ApiBundle\Controller;

use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDoc;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use FOS\RestBundle\Controller\Annotations as Annotations;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * API access to agent groups.
 *
 * @ApiModes("all")
 */
class AgentGroupsController extends BaseController
{
    /**
     * Retrieve the list of agent groups.
     *
     * @ApiDoc(
     *      section = "Agents",
     *      resourceDescription="Operations about agent groups",
     *      description="get agents group list",
     *      statusCodes={
     *          200="Returned if request was successful",
     *      },
     *      output="array<Application\DeskPRO\Entity\Usergroup>"
     * )
     *
     * @Annotations\Get("/agent_groups", name="api_agent_groups")
     */
    public function listAction()
    {
        $service = $this->get('data.user_groups');

        return View::create(
            $this->wrap($service->loadAgentGroupsEnabled()),
            Response::HTTP_OK
        );
    }

    /**
     * Get the agent group with specified id.
     *
     * @ApiDoc(
     *     section = "Agents",
     *     resourceDescription="Operations about agent groups",
     *     description="get agents group list",
     *     requirements={
     *          {
     *              "name"="id",
     *              "requirement"="\d+",
     *              "description"="the id of agent",
     *              "dataType"="integer"
     *          }
     *      },
     *      statusCodes={
     *          200="Returned if request was successful",
     *          404="Returned if we can't find agent group with specified id"
     *      },
     *      output="array<Application\DeskPRO\Entity>"
     * )
     * @Annotations\Get("/agent_groups/{id}", name="api_single_agent_group")
     */
    public function getAgentGroupAction($id)
    {
        $service = $this->get('data.user_groups');

        if (!$agent_group = $service->loadSingleAgentGroupEnabled($id)) {
            throw new NotFoundHttpException(sprintf('Agent group with specified [ %d ] id was not found', $id));
        }

        return View::create(
            $this->wrap($agent_group),
            Response::HTTP_OK
        );
    }
}

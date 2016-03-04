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

namespace DeskPRO\Bundle\ApiBundle\Controller\People;

use Application\DeskPRO\Entity\Person;
use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDoc;
use DeskPRO\Bundle\ApiBundle\Controller\CrudController;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\View\View;

/**
 * Class AgentsController.
 *
 * @ApiModes("all")
 */
class AgentsController extends CrudController
{
    /**
     * @ApiDoc(
     *      description="get a list of all agents w/o pagination",
     *      statusCodes={
     *          200="Success"
     *      }
     * )
     * @Get("/agents", name="api_agents")
     *
     * @return View
     */
    public function getAllAgentsAction()
    {
        $agents = $this->getRepository(Person::class)->findBy(['is_agent' => true]);

        return View::create($this->dataSerialize($agents));
    }

    /**
     * @ApiDoc(
     *      description="get a list of online",
     *      statusCodes={
     *          200="Success"
     *      }
     * )
     * @Get("/online", name="api_agents_online")
     *
     * @return View
     */
    public function getAgentsOnlineAction()
    {
        $agent_ids = $this->get('data.agent')->getOnlineAgentIds();

        return View::create($this->dataSerialize($agent_ids));
    }
}

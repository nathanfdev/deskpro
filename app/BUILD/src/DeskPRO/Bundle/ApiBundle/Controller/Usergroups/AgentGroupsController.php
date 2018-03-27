<?php

namespace DeskPRO\Bundle\ApiBundle\Controller\Usergroups;

use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDoc;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use FOS\RestBundle\Controller\Annotations as Rest;

/**
 * API access to agent groups.
 *
 * @ApiModes("all")
 * @Rest\Route("/agent_groups")
 * @ApiDoc(target="all", section="Agents", output="Application\DeskPRO\Entity\Usergroup")
 */
class AgentGroupsController extends AbstractUserGroupsController
{
    public static $isAgentGroup = true;
}

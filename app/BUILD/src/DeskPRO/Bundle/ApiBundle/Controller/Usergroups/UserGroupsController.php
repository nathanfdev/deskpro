<?php

namespace DeskPRO\Bundle\ApiBundle\Controller\Usergroups;

use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDoc;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use FOS\RestBundle\Controller\Annotations as Rest;

/**
 * Class UserGroupsController.
 *
 * @ApiModes("all")
 * @Rest\Route("/user_groups")
 * @ApiDoc(target="all", section="Usergroups", output="Application\DeskPRO\Entity\Usergroup")
 */
class UserGroupsController extends AbstractUserGroupsController
{
    public static $isAgentGroup = false;
}

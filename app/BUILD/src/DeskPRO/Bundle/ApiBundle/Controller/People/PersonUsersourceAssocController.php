<?php

namespace DeskPRO\Bundle\ApiBundle\Controller\People;

use Application\DeskPRO\Entity\PersonUsersourceAssoc;
use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDoc;
use DeskPRO\Bundle\ApiBundle\Controller\CrudSubController;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use FOS\RestBundle\Controller\Annotations as Rest;

/**
 * Class PersonUsersourceAssocController.
 *
 * @ApiModes("all")
 * @Rest\Route("/people/{parentId}/usersource_assocs")
 * @ApiDoc(target="all", section="People", output="Application\DeskPRO\Entity\PersonUsersourceAssoc")
 */
class PersonUsersourceAssocController extends CrudSubController
{
    public static $exposeOnly     = ['get', 'list', 'count', 'delete'];
    public static $entity         = PersonUsersourceAssoc::class;
    public static $parentProperty = 'person';
}

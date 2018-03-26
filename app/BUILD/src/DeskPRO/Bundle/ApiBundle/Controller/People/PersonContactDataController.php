<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\ApiBundle\Controller\People;

use Application\DeskPRO\Entity\PersonContactData;
use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDoc;
use DeskPRO\Bundle\ApiBundle\Controller\CrudSubController;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use FOS\RestBundle\Controller\Annotations as Rest;

/**
 * Class PersonContactDataController.
 *
 * @ApiModes("all")
 * @Rest\Route("/people/{parentId}/contact_data")
 * @ApiDoc(
 *     target="all",
 *     section="People",
 *     output="DeskPRO\Bundle\AppBundle\Serializer\Model\ContactData\AbstractContactData"
 * )
 */
class PersonContactDataController extends CrudSubController
{
    public static $exposeOnly     = ['get', 'list', 'count'];
    public static $entity         = PersonContactData::class;
    public static $parentProperty = 'person';
    public static $listOrder      = 'asc';
}

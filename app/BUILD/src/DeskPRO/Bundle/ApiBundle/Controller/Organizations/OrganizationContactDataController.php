<?php

namespace DeskPRO\Bundle\ApiBundle\Controller\Organizations;

use Application\DeskPRO\Entity\OrganizationContactData;
use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDoc;
use DeskPRO\Bundle\ApiBundle\Controller\CrudSubController;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use FOS\RestBundle\Controller\Annotations as Rest;

/**
 * Class OrganizationContactDataController.
 *
 * @ApiModes("all")
 * @Rest\Route("/organizations/{parentId}/contact_data")
 * @ApiDoc(
 *     target="all",
 *     section="Organizations",
 *     output="DeskPRO\Bundle\AppBundle\Serializer\Model\ContactData\AbstractContactData"
 * )
 */
class OrganizationContactDataController extends CrudSubController
{
    public static $exposeOnly     = ['get', 'list', 'count'];
    public static $entity         = OrganizationContactData::class;
    public static $parentProperty = 'organization';
    public static $listOrder      = 'asc';
}

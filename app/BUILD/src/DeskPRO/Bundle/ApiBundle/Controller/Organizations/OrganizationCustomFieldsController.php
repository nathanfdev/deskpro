<?php

namespace DeskPRO\Bundle\ApiBundle\Controller\Organizations;

use Application\DeskPRO\Entity\CustomDefOrganization;
use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDoc;
use DeskPRO\Bundle\ApiBundle\Controller\AbstractCustomFieldsController;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use FOS\RestBundle\Controller\Annotations as Rest;

/**
 * Class OrganizationCustomFieldsController.
 *
 * @ApiModes("all")
 * @Rest\Route("/organization_custom_fields")
 * @ApiDoc(target="all", section="Organizations", output="Application\DeskPRO\Entity\CustomDefOrganization")
 * @ApiDoc(
 *     target="postAction,putAction",
 *     input={
 *      "class"="DeskPRO\Bundle\AppBundle\Form\Type\CustomFields\CustomFieldType",
 *      "options"={
 *          "data"="Application\DeskPRO\Entity\CustomDefOrganization"
 *      }
 *     }
 * )
 */
class OrganizationCustomFieldsController extends AbstractCustomFieldsController
{
    public static $entity = CustomDefOrganization::class;
}

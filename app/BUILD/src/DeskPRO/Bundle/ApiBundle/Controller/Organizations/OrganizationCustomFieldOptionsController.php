<?php

namespace DeskPRO\Bundle\ApiBundle\Controller\Organizations;

use Application\DeskPRO\Entity\CustomDefOrganization;
use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDoc;
use DeskPRO\Bundle\ApiBundle\Controller\AbstractCustomFieldOptionsController;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use DeskPRO\Bundle\AppBundle\Serializer\Annotation\SerializerView;
use FOS\RestBundle\Controller\Annotations as Rest;

/**
 * Class OrganizationCustomFieldOptionsController.
 *
 * @ApiModes("all")
 * @Rest\Route("/organization_custom_fields/{parentId}/options")
 * @ApiDoc(target="all", section="Organizations", output="Application\DeskPRO\Entity\CustomDefOrganization")
 * @ApiDoc(
 *     target="postAction,putAction",
 *     input={
 *      "class"="DeskPRO\Bundle\AppBundle\Form\Type\CustomFields\CustomFieldOptionType",
 *      "options"={
 *          "data"="Application\DeskPRO\Entity\CustomDefOrganization",
 *          "parent"="Application\DeskPRO\Entity\CustomDefOrganization"
 *      }
 *     }
 * )
 * @SerializerView(mapping={
 *     "Application\DeskPRO\Entity\CustomDefAbstract": "DeskPRO\Bundle\AppBundle\Serializer\Model\CustomDefChoice"
 * })
 */
class OrganizationCustomFieldOptionsController extends AbstractCustomFieldOptionsController
{
    public static $entity = CustomDefOrganization::class;
}

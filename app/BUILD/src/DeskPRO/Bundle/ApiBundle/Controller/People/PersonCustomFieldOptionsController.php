<?php

namespace DeskPRO\Bundle\ApiBundle\Controller\People;

use Application\DeskPRO\Entity\CustomDefPerson;
use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDoc;
use DeskPRO\Bundle\ApiBundle\Controller\AbstractCustomFieldOptionsController;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use DeskPRO\Bundle\AppBundle\Serializer\Annotation\SerializerView;
use FOS\RestBundle\Controller\Annotations as Rest;

/**
 * Class PersonCustomFieldOptionsController.
 *
 * @ApiModes("all")
 * @Rest\Route("/person_custom_fields/{parentId}/options")
 * @ApiDoc(target="all", section="People", output="Application\DeskPRO\Entity\CustomDefPerson")
 * @ApiDoc(
 *     target="postAction,putAction",
 *     input={
 *      "class"="DeskPRO\Bundle\AppBundle\Form\Type\CustomFields\CustomFieldOptionType",
 *      "options"={
 *          "data"="Application\DeskPRO\Entity\CustomDefPerson",
 *          "parent"="Application\DeskPRO\Entity\CustomDefPerson"
 *      }
 *     }
 * )
 * @SerializerView(mapping={
 *     "Application\DeskPRO\Entity\CustomDefAbstract": "DeskPRO\Bundle\AppBundle\Serializer\Model\CustomDefChoice"
 * })
 */
class PersonCustomFieldOptionsController extends AbstractCustomFieldOptionsController
{
    public static $entity = CustomDefPerson::class;
}

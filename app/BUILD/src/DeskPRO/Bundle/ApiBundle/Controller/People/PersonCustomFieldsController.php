<?php

namespace DeskPRO\Bundle\ApiBundle\Controller\People;

use Application\DeskPRO\Entity\CustomDefPerson;
use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDoc;
use DeskPRO\Bundle\ApiBundle\Controller\AbstractCustomFieldsController;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use FOS\RestBundle\Controller\Annotations as Rest;

/**
 * Class PersonCustomFieldsController.
 *
 * @ApiModes("all")
 * @Rest\Route("/person_custom_fields")
 * @ApiDoc(target="all", section="People", output="Application\DeskPRO\Entity\CustomDefPerson")
 * @ApiDoc(
 *     target="postAction,putAction",
 *     input={
 *      "class"="DeskPRO\Bundle\AppBundle\Form\Type\CustomFields\CustomFieldType",
 *      "options"={
 *          "data"="Application\DeskPRO\Entity\CustomDefPerson"
 *      }
 *     }
 * )
 */
class PersonCustomFieldsController extends AbstractCustomFieldsController
{
    public static $entity = CustomDefPerson::class;
}

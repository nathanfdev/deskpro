<?php

namespace DeskPRO\Bundle\ApiBundle\Controller\Community;

use Application\DeskPRO\Entity\CustomDefCommunityTopic;
use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDoc;
use DeskPRO\Bundle\ApiBundle\Controller\AbstractCustomFieldsController;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use FOS\RestBundle\Controller\Annotations as Rest;

/**
 * Class CommunityCustomFieldsController.
 *
 * @ApiModes("all")
 * @Rest\Route("/community_custom_fields")
 * @ApiDoc(target="all", section="Community", output="Application\DeskPRO\Entity\CustomDefCommunityTopic")
 * @ApiDoc(
 *     target="postAction, putAction",
 *     input={
 *      "class"="DeskPRO\Bundle\AppBundle\Form\Type\CustomFields\CustomFieldType",
 *      "options"={
 *          "data"="Application\DeskPRO\Entity\CustomDefCommunityTopic"
 *      }
 *     }
 * )
 */
class CommunityCustomFieldsController extends AbstractCustomFieldsController
{
    public static $entity = CustomDefCommunityTopic::class;
}

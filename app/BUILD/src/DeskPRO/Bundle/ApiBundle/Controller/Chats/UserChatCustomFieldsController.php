<?php

namespace DeskPRO\Bundle\ApiBundle\Controller\Chats;

use Application\DeskPRO\Entity\CustomDefChat;
use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDoc;
use DeskPRO\Bundle\ApiBundle\Controller\AbstractCustomFieldsController;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use FOS\RestBundle\Controller\Annotations as Rest;

/**
 * Class UserChatCustomFieldsController.
 *
 * @ApiModes("all")
 * @Rest\Route("/user_chat_custom_fields")
 * @ApiDoc(target="all", section="Chats", output="Application\DeskPRO\Entity\CustomDefChat")
 * @ApiDoc(
 *     target="postAction,putAction",
 *     input={
 *      "class"="DeskPRO\Bundle\AppBundle\Form\Type\CustomFields\CustomFieldType",
 *      "options"={
 *          "data"="Application\DeskPRO\Entity\CustomDefChat"
 *      }
 *     }
 * )
 */
class UserChatCustomFieldsController extends AbstractCustomFieldsController
{
    public static $entity = CustomDefChat::class;
}

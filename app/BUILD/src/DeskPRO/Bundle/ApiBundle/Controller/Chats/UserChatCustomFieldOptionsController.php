<?php

namespace DeskPRO\Bundle\ApiBundle\Controller\Chats;

use Application\DeskPRO\Entity\CustomDefChat;
use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDoc;
use DeskPRO\Bundle\ApiBundle\Controller\AbstractCustomFieldOptionsController;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use DeskPRO\Bundle\AppBundle\Serializer\Annotation\SerializerView;
use FOS\RestBundle\Controller\Annotations as Rest;

/**
 * Class UserChatCustomFieldOptionsController.
 *
 * @ApiModes("all")
 * @Rest\Route("/user_chat_custom_fields/{parentId}/options")
 * @ApiDoc(target="all", section="Chats", output="Application\DeskPRO\Entity\CustomDefChat")
 * @ApiDoc(
 *     target="postAction,putAction",
 *     input={
 *      "class"="DeskPRO\Bundle\AppBundle\Form\Type\CustomFields\CustomFieldOptionType",
 *      "options"={
 *          "data"="Application\DeskPRO\Entity\CustomDefChat",
 *          "parent"="Application\DeskPRO\Entity\CustomDefChat"
 *      }
 *     }
 * )
 * @SerializerView(mapping={
 *     "Application\DeskPRO\Entity\CustomDefAbstract": "DeskPRO\Bundle\AppBundle\Serializer\Model\CustomDefChoice"
 * })
 */
class UserChatCustomFieldOptionsController extends AbstractCustomFieldOptionsController
{
    public static $entity = CustomDefChat::class;
}

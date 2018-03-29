<?php

namespace DeskPRO\Bundle\AppBundle\Serializer\Handler\Entity;

use Application\DeskPRO\Entity\ChatConversation;
use Application\DeskPRO\Entity\ChatMessage as ChatMessageEntity;
use DeskPRO\Bundle\AppBundle\Serializer\Model\Chats\ChatMessage;
use DeskPRO\Bundle\AppBundle\Serializer\Sideload\SideloadSerializationContext;

class ChatMessageHandler extends AbstractEntityHandler
{
    /**
     * {@inheritdoc}
     */
    public static function getClassNames()
    {
        return ChatMessageEntity::class;
    }

    /**
     * {@inheritdoc}
     *
     * @param ChatConversation $entity
     */
    public function createModel($entity, SideloadSerializationContext $context)
    {
        return new ChatMessage($entity);
    }
}

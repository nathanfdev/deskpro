<?php

namespace DeskPRO\Bundle\ImportBundle\Writer\Mapper;

use Application\DeskPRO\Entity\CustomDefChat;
use DeskPRO\Bundle\ImportBundle\Model\ChatCustomDef;

/**
 * Class ChatCustomDefMapper.
 */
class CustomDefChatMapper extends AbstractContainerMapper implements CustomDefMapperInterface
{
    /**
     * {@inheritdoc}
     */
    public static function getMapperEntityClass()
    {
        return CustomDefChat::class;
    }

    /**
     * {@inheritdoc}
     */
    public static function getModelClass()
    {
        return ChatCustomDef::class;
    }
}

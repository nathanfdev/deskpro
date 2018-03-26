<?php

namespace DeskPRO\Bundle\ImportBundle\Writer\EntityHandler;

use DeskPRO\Bundle\ImportBundle\Model;

/**
 * Class ChatCustomDefHandler.
 */
class ChatCustomDefHandler extends AbstractCustomDefHandler
{
    /**
     * {@inheritdoc}
     */
    public static function getModelClass()
    {
        return Model\ChatCustomDef::class;
    }

    /**
     * {@inheritdoc}
     */
    protected function getCustomDefMapper()
    {
        return $this->mappers->getChatCustomDefMapper();
    }
}

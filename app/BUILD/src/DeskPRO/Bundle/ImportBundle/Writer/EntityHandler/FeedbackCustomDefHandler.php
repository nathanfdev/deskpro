<?php

namespace DeskPRO\Bundle\ImportBundle\Writer\EntityHandler;

use DeskPRO\Bundle\ImportBundle\Model;

/**
 * Class FeedbackCustomDef.
 */
class FeedbackCustomDefHandler extends AbstractCustomDefHandler
{
    /**
     * {@inheritdoc}
     */
    public static function getModelClass()
    {
        return Model\FeedbackCustomDef::class;
    }

    /**
     * {@inheritdoc}
     */
    protected function getCustomDefMapper()
    {
        return $this->mappers->getFeedbackCustomDefMapper();
    }
}

<?php

namespace DeskPRO\Bundle\ImportBundle\Writer\Mapper;

use Application\DeskPRO\Entity\CustomDefFeedback;
use DeskPRO\Bundle\ImportBundle\Model\FeedbackCustomDef;

/**
 * Custom def ticket record mapper.
 *
 * Class CustomDefFeedback
 */
class CustomDefFeedbackMapper extends AbstractContainerMapper implements CustomDefMapperInterface
{
    /**
     * {@inheritdoc}
     */
    public static function getMapperEntityClass()
    {
        return CustomDefFeedback::class;
    }

    /**
     * {@inheritdoc}
     */
    public static function getModelClass()
    {
        return FeedbackCustomDef::class;
    }
}

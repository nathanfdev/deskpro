<?php

namespace DeskPRO\Bundle\ImportBundle\Writer\Mapper;

use Application\DeskPRO\Entity\CustomDefCommunityTopic;
use DeskPRO\Bundle\ImportBundle\Model\FeedbackCustomDef;

/**
 * Custom def ticket record mapper.
 *
 * Class CustomDefFeedbackMapper
 */
class CustomDefFeedbackMapper extends AbstractContainerMapper implements CustomDefMapperInterface
{
    /**
     * {@inheritdoc}
     */
    public static function getMapperEntityClass()
    {
        return CustomDefCommunityTopic::class;
    }

    /**
     * {@inheritdoc}
     */
    public static function getModelClass()
    {
        return FeedbackCustomDef::class;
    }
}

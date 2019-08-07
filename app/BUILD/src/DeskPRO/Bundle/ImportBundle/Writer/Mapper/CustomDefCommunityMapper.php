<?php

namespace DeskPRO\Bundle\ImportBundle\Writer\Mapper;

use Application\DeskPRO\Entity\CustomDefCommunityTopic;
use DeskPRO\Bundle\ImportBundle\Model\CommunityTopicCustomDef;

/**
 * Custom def community record mapper.
 *
 * Class CustomDefCommunityMapper
 */
class CustomDefCommunityMapper extends AbstractContainerMapper implements CustomDefMapperInterface
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
        return CommunityTopicCustomDef::class;
    }
}

<?php

namespace DeskPRO\Bundle\ImportBundle\Writer\Mapper;

use Application\DeskPRO\Entity\CommunityChannel;

/**
 * Community channel record mapper.
 *
 * Class CommunityChannelMapper
 */
class CommunityChannelMapper extends AbstractCategoryMapper
{
    /**
     * {@inheritdoc}
     */
    public static function getMapperEntityClass()
    {
        return CommunityChannel::class;
    }
}

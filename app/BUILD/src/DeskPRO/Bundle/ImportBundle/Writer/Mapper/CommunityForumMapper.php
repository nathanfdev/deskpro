<?php

namespace DeskPRO\Bundle\ImportBundle\Writer\Mapper;

use Application\DeskPRO\Entity\CommunityForum;

/**
 * Community forum record mapper.
 *
 * Class CommunityForumMapper
 */
class CommunityForumMapper extends AbstractCategoryMapper
{
    /**
     * {@inheritdoc}
     */
    public static function getMapperEntityClass()
    {
        return CommunityForum::class;
    }
}

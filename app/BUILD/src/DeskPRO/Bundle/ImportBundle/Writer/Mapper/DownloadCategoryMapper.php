<?php

namespace DeskPRO\Bundle\ImportBundle\Writer\Mapper;

use Application\DeskPRO\Entity\DownloadCategory;

/**
 * Download category record mapper.
 *
 * Class DownloadCategory
 */
class DownloadCategoryMapper extends AbstractCategoryMapper
{
    /**
     * {@inheritdoc}
     */
    public static function getMapperEntityClass()
    {
        return DownloadCategory::class;
    }
}

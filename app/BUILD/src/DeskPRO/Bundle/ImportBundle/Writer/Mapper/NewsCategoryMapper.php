<?php

namespace DeskPRO\Bundle\ImportBundle\Writer\Mapper;

use Application\DeskPRO\Entity\NewsCategory;

/**
 * News category record mapper.
 *
 * Class NewsCategory
 */
class NewsCategoryMapper extends AbstractCategoryMapper
{
    /**
     * {@inheritdoc}
     */
    public static function getMapperEntityClass()
    {
        return NewsCategory::class;
    }
}

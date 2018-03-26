<?php

namespace DeskPRO\Bundle\ImportBundle\Writer\Mapper;

use Application\DeskPRO\Entity\ArticleCategory;

/**
 * Article category record mapper.
 *
 * Class ArticleCategory
 */
class ArticleCategoryMapper extends AbstractCategoryMapper
{
    /**
     * {@inheritdoc}
     */
    public static function getMapperEntityClass()
    {
        return ArticleCategory::class;
    }
}

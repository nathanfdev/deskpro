<?php

namespace DeskPRO\Bundle\ImportBundle\Writer\Mapper;

use Application\DeskPRO\Entity\FeedbackCategory;

/**
 * Feedback category record mapper.
 *
 * Class FeedbackCategory
 */
class FeedbackCategoryMapper extends AbstractCategoryMapper
{
    /**
     * {@inheritdoc}
     */
    public static function getMapperEntityClass()
    {
        return FeedbackCategory::class;
    }
}

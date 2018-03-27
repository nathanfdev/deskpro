<?php

namespace DeskPRO\Bundle\ImportBundle\Writer\Mapper;

use Application\DeskPRO\Entity\CustomDefArticle;
use DeskPRO\Bundle\ImportBundle\Model\ArticleCustomDef;

/**
 * Custom def article record mapper.
 *
 * Class CustomDefArticle
 */
class CustomDefArticleMapper extends AbstractContainerMapper implements CustomDefMapperInterface
{
    /**
     * {@inheritdoc}
     */
    public static function getMapperEntityClass()
    {
        return CustomDefArticle::class;
    }

    /**
     * {@inheritdoc}
     */
    public static function getModelClass()
    {
        return ArticleCustomDef::class;
    }
}

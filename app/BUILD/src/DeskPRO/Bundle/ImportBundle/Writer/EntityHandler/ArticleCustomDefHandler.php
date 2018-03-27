<?php

namespace DeskPRO\Bundle\ImportBundle\Writer\EntityHandler;

use DeskPRO\Bundle\ImportBundle\Model;

/**
 * Class ArticleCustomDef.
 */
class ArticleCustomDefHandler extends AbstractCustomDefHandler
{
    /**
     * {@inheritdoc}
     */
    public static function getModelClass()
    {
        return Model\ArticleCustomDef::class;
    }

    /**
     * {@inheritdoc}
     */
    protected function getCustomDefMapper()
    {
        return $this->mappers->getArticleCustomDefMapper();
    }
}

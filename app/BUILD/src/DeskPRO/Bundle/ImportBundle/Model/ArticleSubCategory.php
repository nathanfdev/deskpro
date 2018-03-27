<?php

namespace DeskPRO\Bundle\ImportBundle\Model;

/**
 * Exporting article category entity.
 *
 * Class ArticleCategory
 */
class ArticleSubCategory extends AbstractArticleCategory implements ImportMapKeyAwareInterface
{
    use OidAwareModelTrait;

    /**
     * {@inheritdoc}
     */
    public static function getImportMapKey()
    {
        return 'article_category';
    }
}

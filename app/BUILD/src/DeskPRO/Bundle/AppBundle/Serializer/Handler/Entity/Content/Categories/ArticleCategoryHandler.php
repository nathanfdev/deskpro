<?php

namespace DeskPRO\Bundle\AppBundle\Serializer\Handler\Entity\Content\Categories;

use Application\DeskPRO\Entity\ArticleCategory;
use DeskPRO\Bundle\AppBundle\Serializer\Model\Content\Categories\ArticleCategory as ArticleCategoryModel;
use DeskPRO\Bundle\AppBundle\Serializer\Sideload\SideloadSerializationContext;

/**
 * Class ArticleCategoryHandler.
 */
class ArticleCategoryHandler extends AbstractCategoryHandler
{
    /**
     * {@inheritdoc}
     *
     * @param \Application\DeskPRO\Entity\ArticleCategory $entity
     */
    public function createModel($entity, SideloadSerializationContext $context)
    {
        $model = new ArticleCategoryModel($entity);
        $model->setTitleTranslations($this->getTitleTranslations($entity));

        return $model;
    }

    /**
     * {@inheritdoc}
     */
    public static function getClassNames()
    {
        return ArticleCategory::class;
    }
}

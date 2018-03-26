<?php

namespace DeskPRO\Bundle\AppBundle\Serializer\Handler\Entity\Content\Categories;

use Application\DeskPRO\Entity\NewsCategory;
use DeskPRO\Bundle\AppBundle\Serializer\Model\Content\Categories\NewsCategory as NewsCategoryModel;
use DeskPRO\Bundle\AppBundle\Serializer\Sideload\SideloadSerializationContext;

/**
 * Class NewsCategoryHandler.
 */
class NewsCategoryHandler extends AbstractCategoryHandler
{
    /**
     * {@inheritdoc}
     *
     * @param \Application\DeskPRO\Entity\NewsCategory $entity
     */
    public function createModel($entity, SideloadSerializationContext $context)
    {
        $model = new NewsCategoryModel($entity);
        $model->setTitleTranslations($this->getTitleTranslations($entity));

        return $model;
    }

    /**
     * {@inheritdoc}
     */
    public static function getClassNames()
    {
        return NewsCategory::class;
    }
}

<?php

namespace DeskPRO\Bundle\AppBundle\Serializer\Handler\Entity\Content\Categories;

use Application\DeskPRO\Entity\FeedbackCategory;
use DeskPRO\Bundle\AppBundle\Serializer\Model\Content\Categories\FeedbackCategory as FeedbackCategoryModel;
use DeskPRO\Bundle\AppBundle\Serializer\Sideload\SideloadSerializationContext;

/**
 * Class FeedbackCategoryHandler.
 */
class FeedbackCategoryHandler extends AbstractCategoryHandler
{
    /**
     * {@inheritdoc}
     *
     * @param \Application\DeskPRO\Entity\FeedbackCategory $entity
     */
    public function createModel($entity, SideloadSerializationContext $context)
    {
        $model = new FeedbackCategoryModel($entity);
        $model->setTitleTranslations($this->getTitleTranslations($entity));

        return $model;
    }

    /**
     * {@inheritdoc}
     */
    public static function getClassNames()
    {
        return FeedbackCategory::class;
    }
}

<?php

namespace DeskPRO\Bundle\AppBundle\Serializer\Handler\Entity\Content\Categories;

use Application\DeskPRO\Entity\DownloadCategory;
use DeskPRO\Bundle\AppBundle\Serializer\Model\Content\Categories\DownloadCategory as DownloadCategoryModel;
use DeskPRO\Bundle\AppBundle\Serializer\Sideload\SideloadSerializationContext;

/**
 * Class DownloadCategoryHandler.
 */
class DownloadCategoryHandler extends AbstractCategoryHandler
{
    /**
     * {@inheritdoc}
     *
     * @param \Application\DeskPRO\Entity\DownloadCategory $entity
     */
    public function createModel($entity, SideloadSerializationContext $context)
    {
        $model = new DownloadCategoryModel($entity);
        $model->setTitleTranslations($this->getTitleTranslations($entity));

        return $model;
    }

    /**
     * {@inheritdoc}
     */
    public static function getClassNames()
    {
        return DownloadCategory::class;
    }
}

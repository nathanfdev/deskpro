<?php

namespace DeskPRO\Bundle\AppBundle\Serializer\Handler\Entity\Content\Categories;

use Application\DeskPRO\Entity\Product;
use DeskPRO\Bundle\AppBundle\Serializer\Model\Content\Categories\Product as ProductModel;
use DeskPRO\Bundle\AppBundle\Serializer\Sideload\SideloadSerializationContext;

/**
 * Class ProductHandler.
 */
class ProductHandler extends AbstractCategoryHandler
{
    /**
     * {@inheritdoc}
     *
     * @param \Application\DeskPRO\Entity\Product $entity
     */
    public function createModel($entity, SideloadSerializationContext $context)
    {
        $model = new ProductModel($entity);
        $model->setTitleTranslations($this->getTitleTranslations($entity));

        return $model;
    }

    /**
     * {@inheritdoc}
     */
    public static function getClassNames()
    {
        return Product::class;
    }
}

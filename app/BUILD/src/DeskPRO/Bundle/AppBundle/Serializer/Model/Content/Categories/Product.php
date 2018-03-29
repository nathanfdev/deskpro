<?php

namespace DeskPRO\Bundle\AppBundle\Serializer\Model\Content\Categories;

use Application\DeskPRO\Entity\Product as ProductEntity;
use JMS\Serializer\Annotation as JMS;

/**
 * Class Product.
 */
class Product extends CategoryAbstract
{
    /**
     * Parent of the product.
     *
     * @JMS\Groups("details")
     * @JMS\Type("entity<Application\DeskPRO\Entity\Product>")
     *
     * @var \Application\DeskPRO\Entity\Product
     */
    protected $parent;

    /**
     * Constructor.
     *
     * @param ProductEntity $entity
     */
    public function __construct(ProductEntity $entity)
    {
        parent::__construct($entity);

        $this->parent = $entity->getParent();
    }
}

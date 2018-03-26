<?php

namespace DeskPRO\Bundle\AppBundle\Serializer\Model\Content\Categories;

use Application\DeskPRO\Entity\Brand;
use Application\DeskPRO\Entity\NewsCategory as NewsCategoryEntity;
use Application\DeskPRO\Entity\Usergroup;
use JMS\Serializer\Annotation as JMS;

/**
 * Class NewsCategory.
 */
class NewsCategory extends CategoryAbstract
{
    /**
     * Category`s parent.
     *
     * @JMS\Groups("news_categories")
     * @JMS\Type("entity<Application\DeskPRO\Entity\NewsCategory>")
     *
     * @var \Application\DeskPRO\Entity\NewsCategory
     */
    protected $parent;

    /**
     * Category`s children.
     *
     * @JMS\Groups("news_categories")
     * @JMS\Type("collection<entity<Application\DeskPRO\Entity\NewsCategory>>")
     *
     * @var \Application\DeskPRO\Entity\NewsCategory[]
     */
    protected $children;

    /**
     * Usergroups that has access to this category.
     *
     * @JMS\Groups("news_categories")
     * @JMS\Type("collection<entity<Application\DeskPRO\Entity\Usergroup>>")
     *
     * @var Usergroup[]
     */
    protected $usergroups;

    /**
     * Brand linked to the category.
     *
     * @JMS\Groups("news_categories")
     * @JMS\Type("entity<Application\DeskPRO\Entity\Brand>")
     *
     * @var Brand
     */
    protected $brand;

    /**
     * Constructor.
     *
     * @param NewsCategoryEntity $entity
     */
    public function __construct(NewsCategoryEntity $entity)
    {
        parent::__construct($entity);

        $this->parent     = $entity->getParent();
        $this->children   = $entity->getChildren();
        $this->usergroups = $entity->getUserGroups();
        $this->brand      = $entity->getBrand();
    }
}

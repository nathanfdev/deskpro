<?php

namespace DeskPRO\Bundle\AppBundle\Serializer\Model\Content\Categories;

use Application\DeskPRO\Entity\Brand;
use Application\DeskPRO\Entity\DownloadCategory as DownloadCategoryEntity;
use Application\DeskPRO\Entity\Usergroup;
use JMS\Serializer\Annotation as JMS;

/**
 * Class DownloadCategory.
 */
class DownloadCategory extends CategoryAbstract
{
    /**
     * Category`s parent.
     *
     * @JMS\Groups("download_categories")
     * @JMS\Type("entity<Application\DeskPRO\Entity\DownloadCategory>")
     *
     * @var \Application\DeskPRO\Entity\DownloadCategory
     */
    protected $parent;

    /**
     * Category`s children.
     *
     * @JMS\Groups("download_categories")
     * @JMS\Type("collection<entity<Application\DeskPRO\Entity\DownloadCategory>>")
     *
     * @var \Application\DeskPRO\Entity\DownloadCategory[]
     */
    protected $children;

    /**
     * Usergroups that has access to this category.
     *
     * @JMS\Groups("download_categories")
     * @JMS\Type("collection<entity<Application\DeskPRO\Entity\Usergroup>>")
     *
     * @var Usergroup[]
     */
    protected $usergroups;

    /**
     * Brand linked to the category.
     *
     * @JMS\Groups("download_categories")
     * @JMS\Type("entity<Application\DeskPRO\Entity\Brand>")
     *
     * @var Brand
     */
    protected $brand;

    /**
     * Constructor.
     *
     * @param DownloadCategoryEntity $entity
     */
    public function __construct(DownloadCategoryEntity $entity)
    {
        parent::__construct($entity);

        $this->parent     = $entity->getParent();
        $this->children   = $entity->getChildren();
        $this->usergroups = $entity->getUserGroups();
        $this->brand      = $entity->getBrand();
    }
}

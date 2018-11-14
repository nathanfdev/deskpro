<?php

namespace DeskPRO\Bundle\AppBundle\Serializer\Model;

use Application\DeskPRO\Entity\Brand as BrandEntity;
use Application\DeskPRO\Entity\Department as DepartmentEntity;
use DeskPRO\Bundle\AppBundle\Content\Avatar;
use JMS\Serializer\Annotation as JMS;

/**
 * Class Department.
 */
class Department
{
    /**
     * The unique ID.
     *
     * @JMS\Type("integer")
     *
     * @var int
     */
    protected $id;

    /**
     * Parent Department entity.
     *
     * @JMS\Type("entity<Application\DeskPRO\Entity\Department>")
     *
     * @var DepartmentEntity
     */
    protected $parent = null;

    /**
     * Children Department entity.
     *
     * @JMS\Type("array<entity<Application\DeskPRO\Entity\Department>>")
     *
     * @var DepartmentEntity
     */
    protected $children = null;

    /**
     * Department`s title.
     *
     * @JMS\Type("string")
     *
     * @var string
     */
    protected $title;

    /**
     * Given by user Department`s title.
     *
     * @JMS\Type("string")
     *
     * @var string
     */
    protected $userTitle = '';

    /**
     * Are tickets enabled for this Department?
     *
     *
     * @JMS\Type("boolean")
     *
     * @var bool
     */
    protected $isTicketsEnabled = true;

    /**
     * Are chats enabled for this Department?
     *
     * @JMS\Type("boolean")
     *
     * @var bool
     */
    protected $isChatEnabled = true;

    /**
     * Department display order.
     *
     * @JMS\Type("integer")
     *
     * @var int
     */
    protected $displayOrder;

    /**
     * Avatar for this department.
     *
     * @JMS\Type("DeskPRO\Bundle\AppBundle\Content\Avatar")
     *
     * @var Avatar
     */
    protected $avatar;

    /**
     * Department brands.
     *
     * @JMS\Type("array<entity<Application\DeskPRO\Entity\Brand>>")
     *
     * @var BrandEntity[]
     */
    protected $brands;

    /**
     * Constructor.
     *
     * @param DepartmentEntity $department
     * @param Avatar           $avatar
     */
    public function __construct(DepartmentEntity $department, Avatar $avatar)
    {
        $this->id               = $department->getId();
        $this->parent           = $department->getParent();
        $this->children         = $department->getChildrenOrdered();
        $this->title            = $department->getTitle();
        $this->userTitle        = $department->getUserTitle();
        $this->isChatEnabled    = $department->isChatEnabled();
        $this->isTicketsEnabled = $department->isTicketsEnabled();
        $this->displayOrder     = $department->getDisplayOrder();
        $this->brands           = $department->getBrands();
        $this->avatar           = $avatar;
    }
}

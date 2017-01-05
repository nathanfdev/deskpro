<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2017, DeskPRO Ltd.
 *
 * The license agreement under which this software is released
 * can be found at https://www.deskpro.com/eula/
 *
 * By using this software, you acknowledge having read the license
 * and agree to be bound thereby.
 *
 * Please note that DeskPRO is not free software. We release the full
 * source code for our software because we trust our users to pay us for
 * the huge investment in time and energy that has gone into both creating
 * this software and supporting our customers. By providing the source code
 * we preserve our customers' ability to modify, audit and learn from our
 * work. We have been developing DeskPRO since 2001, please help us make it
 * another decade.
 *
 * Like the work you see? Think you could make it better? We are always
 * looking for great developers to join us: http://www.deskpro.com/jobs/
 *
 * ~ Thanks, Everyone at Team DeskPRO
 */

namespace DeskPRO\Bundle\AppBundle\Serializer\Model;

use Application\DeskPRO\Entity\Brand;
use Application\DeskPRO\Entity\Department as DepartmentEntity;
use Application\DeskPRO\Entity\Person;
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
    protected $user_title = '';

    /**
     * Are tickets enabled for this Department?
     *
     *
     * @JMS\Type("boolean")
     *
     * @var bool
     */
    protected $is_tickets_enabled = true;

    /**
     * Are chats enabled for this Department?
     *
     * @JMS\Type("boolean")
     *
     * @var bool
     */
    protected $is_chat_enabled = true;

    /**
     * Department display order.
     *
     * @JMS\Type("integer")
     *
     * @var int
     */
    protected $display_order;

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
     * @var Brand[]
     */
    protected $brands;

    /**
     * Agents belong to department.
     *
     * @JMS\Type("array<entity<Application\DeskPRO\Entity\Person>>")
     *
     * @var Person[]
     */
    protected $agents;

    /**
     * Constructor.
     *
     * @param DepartmentEntity $department
     * @param Avatar           $avatar
     */
    public function __construct(DepartmentEntity $department, Avatar $avatar)
    {
        $this->id                 = $department->getId();
        $this->parent             = $department->getParent();
        $this->title              = $department->getTitle();
        $this->user_title         = $department->getUserTitle();
        $this->is_chat_enabled    = $department->isChatEnabled();
        $this->is_tickets_enabled = $department->isTicketsEnabled();
        $this->display_order      = $department->getDisplayOrder();
        $this->brands             = $department->getBrands();
        $this->agents             = $department->getPersonList();
    }
}

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

namespace DeskPRO\Bundle\AppBundle\Serializer\Model\Content\Categories;

use Application\DeskPRO\Entity\FeedbackCategory as FeedbackCategoryEntity;
use Application\DeskPRO\Entity\Usergroup;
use JMS\Serializer\Annotation as JMS;

/**
 * Class FeedbackCategory.
 */
class FeedbackCategory extends CategoryAbstract
{
    /**
     * Category`s parent.
     *
     * @JMS\Type("entity<Application\DeskPRO\Entity\FeedbackCategory>")
     *
     * @var \Application\DeskPRO\Entity\FeedbackCategory
     */
    protected $parent;

    /**
     * Category`s children.
     *
     * @JMS\Type("collection<entity<Application\DeskPRO\Entity\FeedbackCategory>>")
     *
     * @var \Application\DeskPRO\Entity\FeedbackCategory[]
     */
    protected $children;

    /**
     * Usergroups that has access to this category.
     *
     * @JMS\Type("collection<entity<Application\DeskPRO\Entity\Usergroup>>")
     *
     * @var Usergroup[]
     */
    protected $usergroups;

    /**
     * Constructor.
     *
     * @param FeedbackCategoryEntity $entity
     */
    public function __construct(FeedbackCategoryEntity $entity)
    {
        parent::__construct($entity);

        $this->parent     = $entity->getParent();
        $this->children   = $entity->getChildren();
        $this->usergroups = $entity->getUserGroups();
    }
}

<?php

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

<?php

namespace DeskPRO\Bundle\AppBundle\Serializer\Model\Content\Categories;

use Application\DeskPRO\Entity\CommunityForum as CommunityForumEntity;
use Application\DeskPRO\Entity\Usergroup;
use JMS\Serializer\Annotation as JMS;

/**
 * Class CommunityForum.
 */
class CommunityForum extends CategoryAbstract
{
    /**
     * Forums's parent.
     *
     * @JMS\Type("entity<Application\DeskPRO\Entity\CommunityForum>")
     *
     * @var \Application\DeskPRO\Entity\CommunityForum
     */
    protected $parent;

    /**
     * Forum's children.
     *
     * @JMS\Type("collection<entity<Application\DeskPRO\Entity\CommunityForum>>")
     *
     * @var \Application\DeskPRO\Entity\CommunityForum[]
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
     * Custom fields per community.
     *
     * @JMS\Type("collection<entity<Application\DeskPRO\Entity\CustomDefCommunityTopic>>")
     *
     * @var Usergroup[]
     */
    protected $customFields;

    /**
     * Constructor.
     *
     * @param CommunityForumEntity $entity
     */
    public function __construct(CommunityForumEntity $entity)
    {
        parent::__construct($entity);

        $this->parent       = $entity->getParent();
        $this->children     = $entity->getChildren();
        $this->usergroups   = $entity->getUserGroups();
        $this->customFields = $entity->getTopicFields();
    }
}

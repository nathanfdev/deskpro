<?php

namespace DeskPRO\Bundle\AppBundle\Serializer\Model\Content\Categories;

use Application\DeskPRO\Entity\CommunityForum as CommunityForumEntity;
use Application\DeskPRO\Entity\CommunityForumToCustomDefCommunityTopic;
use Application\DeskPRO\Entity\CustomDefCommunityTopic;
use Application\DeskPRO\Entity\Phrase;
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
     * @var CustomDefCommunityTopic[]
     */
    protected $customFields;

    /**
     * Forum`s noun
     *
     * @JMS\Type("string")
     * @JMS\Groups("list")
     *
     * @var string
     */
    protected $noun;

    /**
     * Forum`s plural
     *
     * @JMS\Type("string")
     * @JMS\Groups("list")
     *
     * @var string
     */
    protected $plural;

    /**
     * @JMS\Type("array<DeskPRO\Bundle\AppBundle\Serializer\Model\Phrase>")
     *
     * @var Phrase[]
     */
    protected $noonTranslations;

    /**
     * @JMS\Type("array<DeskPRO\Bundle\AppBundle\Serializer\Model\Phrase>")
     *
     * @var Phrase[]
     */
    protected $pluralTranslations;

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
        $this->customFields = $entity->getTopicFields()->map(function (CommunityForumToCustomDefCommunityTopic $pivot) {
            return $pivot->getField();
        });
    }

    /**
     * @param \Application\DeskPRO\Entity\Phrase[] $noonTranslations
     *
     * @return $this
     */
    public function setNoonTranslations(array $noonTranslations)
    {
        $this->noonTranslations = $noonTranslations;

        return $this;
    }

    /**
     * @param \Application\DeskPRO\Entity\Phrase[] $pluralTranslations
     *
     * @return $this
     */
    public function setPluralTranslations(array $pluralTranslations)
    {
        $this->pluralTranslations = $pluralTranslations;

        return $this;
    }
}

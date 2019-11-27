<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\Entity;

use Application\DeskPRO\Validator\HasValidationMetadataInterface;
use DeskPRO\Bundle\AppBundle\Entity\HasSplashImageProperty;
use DeskPRO\Bundle\AppBundle\Entity\IconProperty;
use DeskPRO\Bundle\AppBundle\Entity\SplashImageProperty;
use DeskPRO\Bundle\AppBundle\EventListener\Doctrine\CommunityForumListener;
use DeskPRO\Bundle\AppBundle\ObjectRouter\Configuration\PortalLinkCustom;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Events;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Mapping\ClassMetadata as ValidatorClassMetadata;

/**
 * Community forums.
 *
 * @JMS\ExclusionPolicy("all")
 *
 * @PortalLinkCustom()
 */
class CommunityForum extends CategoryAbstract implements HasValidationMetadataInterface, HasSplashImageProperty
{
    /**
     * @var string|null
     */
    protected $description;

    /**
     * @var CommunityForum
     */
    protected $parent;

    /**
     * @var CommunityForum[]
     */
    protected $children;

    /**
     * @var \Doctrine\Common\Collections\ArrayCollection
     */
    protected $usergroups;

    /**
     * @var Brand
     */
    protected $brand;

    /**
     * @var ArrayCollection|CommunityForumToStatus[]
     *
     * As set of topic statuses that may be used with this forum
     */
    protected $topic_statuses;

    /**
     * @var ArrayCollection|CommunityForumToCustomDefCommunityTopic[]
     *
     * A set of custom fields that may be used with this forum
     */
    protected $topic_fields;

    /**
     * @var bool
     *
     * If TRUE then voting is enabled for this forum
     */
    protected $is_voting_enabled = true;

    /**
     * @var SplashImageProperty
     */
    protected $splash_image_property;

    /**
     * @var string
     */
    protected $color;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->usergroups     = new ArrayCollection();
        $this->topic_statuses = new ArrayCollection();
        $this->topic_fields   = new ArrayCollection();
    }

    /**
     * @return string|null
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param string|null $description
     *
     * @return CommunityForum
     */
    public function setDescription($description)
    {
        $this->setModelField('description', $description);

        return $this;
    }

    /**
     * @return Brand
     */
    public function getBrand()
    {
        return $this->brand;
    }

    /**
     * @param Brand $brand
     *
     * @return $this
     */
    public function setBrand(Brand $brand = null)
    {
        $this->setModelField('brand', $brand);

        return $this;
    }

    /**
     * @return CommunityForum
     */
    public static function createCommunityForum()
    {
        $category = new self();

        return $category;
    }

    /**
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getUserGroups()
    {
        return $this->usergroups;
    }

    /**
     * @param \Application\DeskPRO\Entity\Usergroup $usergroup
     */
    public function addUsergroup(Usergroup $usergroup)
    {
        if (!$this->usergroups->contains($usergroup)) {
            $this->usergroups->add($usergroup);
        }
    }

    /**
     * @param \Application\DeskPRO\Entity\Usergroup $usergroup
     */
    public function removeUsergroup(Usergroup $usergroup)
    {
        $this->usergroups->removeElement($usergroup);
    }

    /**
     * {@inheritdoc}
     */
    public function toApiData($primary = true, $deep = true, array $visited = [])
    {
        $data = parent::toApiData($primary, $deep, $visited);

        $data['brand'] = $this->brand ? $this->brand->getId() : null;
        if ($splashImage = $this->getSplashImage()) {
            if ($splashImage->getUrnNs() === $splashImage::$unsplashNs) {
                $data['custom_splash_image'] = $splashImage->getOptions()['url'].'&w=200';
            } elseif ($splashImage->getUrnNs() === $splashImage::$blobNs) {
                $data['custom_splash_image'] = $splashImage->getBlob()->getThumbnailUrl(200, true);
            }
        }
        if ($iconProperty = $this->getIcon()) {
            if ($iconProperty->getUrnNs() === $iconProperty::$faNs) {
                $data['icon_property']['urn']   = $iconProperty->getUrn();
                $data['icon_property']['style'] = $iconProperty->getOptions()['style'];
                $data['icon_property']['color'] = $iconProperty->getOptions()['color'];
            }
        }

        return $data;
    }

    /**
     * @return CommunityTopicStatusCategory[]|ArrayCollection
     */
    public function getTopicCategoryStatuses()
    {
        return $this->topic_statuses->map(function (CommunityForumToStatus $pivot) {
            return $pivot->getStatus();
        });
    }

    public function getTopicStatuses()
    {
        return $this->topic_statuses;
    }

    /**
     * @return CustomDefCommunityTopic[]|ArrayCollection
     */
    public function getTopicFields()
    {
        return $this->topic_fields->map(function (CommunityForumToCustomDefCommunityTopic $pivot) {
            return $pivot->getField();
        });
    }

    /**
     * @return CommunityForumToCustomDefCommunityTopic[]|ArrayCollection
     */
    public function getTopicJunctionFields()
    {
        return $this->topic_fields;
    }

    /**
     * @return bool
     */
    public function isVotingEnabled()
    {
        return $this->is_voting_enabled;
    }

    /**
     * @param bool $is_voting_enabled
     *
     * @return CommunityForum
     */
    public function setIsVotingEnabled($is_voting_enabled)
    {
        $this->setModelField('is_voting_enabled', (bool) $is_voting_enabled);

        return $this;
    }

    /**
     * @return SplashImageProperty
     */
    public function getSplashImage()
    {
        return $this->splash_image_property;
    }

    /**
     * @param SplashImageProperty $splashImageProperty
     *
     * @return mixed
     */
    public function setSplashImage($splashImageProperty)
    {
        $this->setModelField('splash_image_property', $splashImageProperty);

        return $this;
    }

    //###########################################################################
    // Validation Metadata
    //###########################################################################

    public static function loadValidatorMetadata(ValidatorClassMetadata $metadata)
    {
        $metadata->addPropertyConstraint('title', new NotBlank());
    }

    //###########################################################################
    // Doctrine Metadata
    //###########################################################################

    public static function loadMetadata(ClassMetadata $metadata)
    {
        $metadata->addEntityListener(Events::prePersist, CommunityForumListener::class, 'prePersist');
        $metadata->addEntityListener(Events::preUpdate, CommunityForumListener::class, 'preUpdate');
        $metadata->setInheritanceType(ClassMetadataInfo::INHERITANCE_TYPE_NONE);
        $metadata->customRepositoryClassName = \Application\DeskPRO\EntityRepository\CommunityForum::class;
        $metadata->setPrimaryTable(['name' => 'community_forums']);
        $metadata->setChangeTrackingPolicy(ClassMetadataInfo::CHANGETRACKING_NOTIFY);
        $metadata->mapField(
            [
                'fieldName'  => 'id',
                'type'       => 'integer',
                'precision'  => 0,
                'scale'      => 0,
                'nullable'   => false,
                'columnName' => 'id',
                'id'         => true,
            ]
        );
        $metadata->mapField(
            [
                'fieldName'  => 'title',
                'type'       => 'string',
                'length'     => 255,
                'precision'  => 0,
                'scale'      => 0,
                'nullable'   => false,
                'columnName' => 'title',
            ]
        );
        $metadata->mapField(
            [
                'fieldName'  => 'description',
                'type'       => 'text',
                'nullable'   => true,
                'columnName' => 'description',
            ]
        );
        $metadata->mapField(
            [
                'fieldName'  => 'slug',
                'type'       => 'string',
                'length'     => 255,
                'precision'  => 0,
                'scale'      => 0,
                'nullable'   => false,
                'columnName' => 'slug',
                'unique'     => true,
            ]
        );
        $metadata->mapField(
            [
                'fieldName'  => 'color',
                'type'       => 'string',
                'length'     => 6,
                'precision'  => 0,
                'scale'      => 0,
                'nullable'   => true,
                'columnName' => 'color',
                'unique'     => true,
            ]
        );
        $metadata->mapField(
            [
                'fieldName'  => 'display_order',
                'type'       => 'integer',
                'precision'  => 0,
                'scale'      => 0,
                'nullable'   => false,
                'columnName' => 'display_order',
            ]
        );
        $metadata->mapField(
            [
                'fieldName'  => 'depth',
                'type'       => 'integer',
                'precision'  => 0,
                'scale'      => 0,
                'nullable'   => false,
                'columnName' => 'depth',
            ]
        );
        $metadata->mapField(
            [
                'fieldName'  => 'root',
                'type'       => 'integer',
                'precision'  => 0,
                'scale'      => 0,
                'nullable'   => true,
                'columnName' => 'root',
            ]
        );
        $metadata->mapField(
            [
                'fieldName'  => 'is_voting_enabled',
                'type'       => 'boolean',
                'precision'  => 0,
                'scale'      => 0,
                'nullable'   => false,
                'columnName' => 'is_voting_enabled',
            ]
        );
        $metadata->setIdGeneratorType(ClassMetadataInfo::GENERATOR_TYPE_IDENTITY);
        $metadata->mapManyToOne(
            [
                'fieldName'    => 'parent',
                'targetEntity' => self::class,
                'mappedBy'     => null,
                'inversedBy'   => 'children',
                'joinColumns'  => [
                    0 => [
                        'name'                 => 'parent_id',
                        'referencedColumnName' => 'id',
                        'onDelete'             => 'set null',
                    ],
                ],
            ]
        );
        $metadata->mapOneToMany(
            [
                'fieldName'    => 'children',
                'targetEntity' => self::class,
                'mappedBy'     => 'parent',
                'orderBy'      => ['display_order' => 'ASC'],
            ]
        );
        $metadata->mapManyToMany(
            [
                'fieldName'    => 'usergroups',
                'targetEntity' => 'Application\\DeskPRO\\Entity\\Usergroup',
                'cascade'      => [
                    'persist',
                    'merge',
                ],
                'joinTable' => [
                    'name'        => 'community_forum2usergroup',
                    'schema'      => null,
                    'joinColumns' => [
                        0 => [
                            'name'                 => 'community_forum_id',
                            'referencedColumnName' => 'id',
                            'nullable'             => true,
                            'columnDefinition'     => null,
                            'onDelete'             => 'cascade',
                        ],
                    ],
                    'inverseJoinColumns' => [
                        0 => [
                            'name'                 => 'usergroup_id',
                            'onDelete'             => 'cascade',
                            'nullable'             => true,
                            'columnDefinition'     => null,
                            'referencedColumnName' => 'id',
                        ],
                    ],
                ],
            ]
        );
        $metadata->mapManyToOne(
            [
                'fieldName'    => 'brand',
                'targetEntity' => Brand::class,
                'cascade'      => ['persist'],
                'joinColumns'  => [
                    [
                        'name'                 => 'brand_id',
                        'referencedColumnName' => 'id',
                        'nullable'             => true,
                        'onDelete'             => 'set null',
                    ],
                ],
                'dpApi' => true,
            ]
        );
        $metadata->mapManyToOne(
            [
                'fieldName'    => 'icon_property',
                'targetEntity' => IconProperty::class,
                'mappedBy'     => null,
                'inversedBy'   => null,
                'joinColumns'  => [
                    0 => [
                        'name'                 => 'icon_property_id',
                        'referencedColumnName' => 'id',
                        'nullable'             => true,
                        'onDelete'             => 'set null',
                        'columnDefinition'     => null,
                    ],
                ],
                'dpApi' => true,
            ]
        );
        $metadata->mapOneToMany(
            [
                'fieldName'    => 'topic_statuses',
                'targetEntity' => CommunityForumToStatus::class,
                'mappedBy'     => 'forum',
                'orderBy'      => ['display_order' => 'ASC'],
            ]
        );
        $metadata->mapOneToMany(
            [
                'fieldName'    => 'topic_fields',
                'targetEntity' => CommunityForumToCustomDefCommunityTopic::class,
                'mappedBy'     => 'forum',
                'orderBy'      => ['display_order' => 'ASC'],
            ]
        );
        $metadata->mapManyToOne(
            [
                'fieldName'    => 'splash_image_property',
                'targetEntity' => SplashImageProperty::class,
                'fetch'        => ClassMetadataInfo::FETCH_EAGER,
                'mappedBy'     => null,
                'inversedBy'   => null,
                'joinColumns'  => [
                    0 => [
                        'name'                 => 'splash_image_property_id',
                        'referencedColumnName' => 'id',
                        'nullable'             => true,
                        'onDelete'             => 'set null',
                        'columnDefinition'     => null,
                    ],
                ],
                'dpApi' => true,
            ]
        );
    }
}

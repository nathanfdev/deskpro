<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\Entity;

use Application\DeskPRO\Validator\HasValidationMetadataInterface;
use DeskPRO\Bundle\AppBundle\EventListener\Doctrine\FeedbackCategoryListener;
use DeskPRO\Bundle\AppBundle\ObjectRouter\Configuration\PortalLinkCustom;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Events;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Mapping\ClassMetadata as ValidatorClassMetadata;

/**
 * Feedback categories. (These are referred to in code/urls as "types").
 *
 * @JMS\ExclusionPolicy("all")
 *
 * @PortalLinkCustom()
 */
class FeedbackCategory extends CategoryAbstract implements HasValidationMetadataInterface
{
    protected $parent;

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
     * Constructor.
     */
    public function __construct()
    {
        $this->usergroups = new ArrayCollection();
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
     * @return FeedbackCategory
     */
    public static function createFeedbackCategory()
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

        return $data;
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
        $metadata->addEntityListener(Events::prePersist, FeedbackCategoryListener::class, 'prePersist');
        $metadata->addEntityListener(Events::preUpdate, FeedbackCategoryListener::class, 'preUpdate');
        $metadata->setInheritanceType(ClassMetadataInfo::INHERITANCE_TYPE_NONE);
        $metadata->customRepositoryClassName = 'Application\DeskPRO\EntityRepository\FeedbackCategory';
        $metadata->setPrimaryTable(['name' => 'feedback_categories']);
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
        $metadata->setIdGeneratorType(ClassMetadataInfo::GENERATOR_TYPE_IDENTITY);
        $metadata->mapManyToOne(
            [
                'fieldName'    => 'parent',
                'targetEntity' => 'Application\\DeskPRO\\Entity\\FeedbackCategory',
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
                'targetEntity' => 'Application\\DeskPRO\\Entity\\FeedbackCategory',
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
                    'name'        => 'feedback_category2usergroup',
                    'schema'      => null,
                    'joinColumns' => [
                        0 => [
                            'name'                 => 'category_id',
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
    }
}

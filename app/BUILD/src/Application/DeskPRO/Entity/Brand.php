<?php

namespace Application\DeskPRO\Entity;

use Application\DeskPRO\Domain\DomainObject;
use Application\DeskPRO\EntityRepository\Brand as BrandRepository;
use DeskPRO\Bundle\AppBundle\Entity\ThemeSet;
use DeskPRO\Bundle\AppBundle\EventListener\Doctrine\BrandListener;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Events;
use Doctrine\ORM\Mapping\ClassMetadata;
use Orb\Doctrine\ORM\Mapping\Builder\ClassMetadataBuilder;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @property int    $id
 * @property string $name
 * @property string $theme_id
 * @property Blob   $logo_blob
 *
 * @UniqueEntity("url")
 */
class Brand extends DomainObject
{
    /**
     * The unique ID.
     *
     * @var int
     */
    protected $id = null;

    /**
     * The brand name.
     *
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $slug;

    /**
     * The brand url.
     *
     * @var string
     */
    protected $url;

    /**
     * @var \DeskPRO\Bundle\AppBundle\Entity\ThemeSet
     */
    protected $theme_set;

    /**
     * @var \DeskPRO\Bundle\AppBundle\Entity\ThemeSet
     */
    protected $edit_theme_set;

    /**
     * @var \Doctrine\Common\Collections\ArrayCollection
     */
    protected $departments;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->departments = new ArrayCollection();
    }

    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return ThemeSet
     */
    public function getThemeSet()
    {
        return $this->theme_set;
    }

    /**
     * @param ThemeSet $theme_set
     */
    public function setThemeSet(ThemeSet $theme_set)
    {
        $this->setModelField('theme_set', $theme_set);
    }

    /**
     * @return ThemeSet
     */
    public function getEditThemeSet()
    {
        return $this->edit_theme_set;
    }

    /**
     * @param ThemeSet $theme_set
     */
    public function setEditThemeSet(ThemeSet $theme_set)
    {
        $this->setModelField('edit_theme_set', $theme_set);
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     *
     * @return $this
     */
    public function setName($name)
    {
        $this->setModelField('name', $name);

        return $this;
    }

    /**
     * @return string
     */
    public function getSlug()
    {
        return $this->slug;
    }

    /**
     * @param string $slug
     *
     * @return $this
     */
    public function setSlug($slug)
    {
        $this->setModelField('slug', $slug);

        return $this;
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @param string $url
     *
     * @return $this
     */
    public function setUrl($url)
    {
        $this->setModelField('url', $url);

        return $this;
    }

    /**
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getDepartments()
    {
        return $this->departments;
    }

    /**
     * @return ArrayCollection|Department[]
     */
    public function getTicketDepartments()
    {
        return $this->departments->filter(function (Department $department) {
            return $department->isTicketsEnabled();
        });
    }

    /**
     * @return ArrayCollection|Department[]
     */
    public function getChatDepartments()
    {
        return $this->departments->filter(function (Department $department) {
            return $department->isChatEnabled();
        });
    }

    /**
     * @param Department $department
     *
     * @return $this
     */
    public function addDepartment(Department $department)
    {
        if (!$this->departments->contains($department)) {
            $this->departments[] = $department;
        }

        return $this;
    }

    /**
     * @param Department $department
     *
     * @return $this
     */
    public function removeDepartment(Department $department)
    {
        $this->departments->removeElement($department);
        $this->_onPropertyChanged('departments', null, $this->departments);

        return $this;
    }

    /**
     * @param Department $department
     *
     * @return bool
     */
    public function hasDepartment(Department $department)
    {
        return $this->departments->contains($department);
    }

    public function __toString()
    {
        return $this->getName();
    }

    //###########################################################################
    // Doctrine Metadata
    //###########################################################################

    public static function loadMetadata(ClassMetadata $metadata)
    {
        $builder = new ClassMetadataBuilder($metadata);
        $builder->setCustomRepositoryClass(BrandRepository::class);
        $builder->setChangeTrackingPolicyNotify();
        $builder->setTable('brands');

        $builder->mapId();
        $builder->mapString('name');
        $builder->mapString('slug', 255, false, true);
        $builder->mapString('url', 255, true, true);
        $builder->createOneToOne('theme_set', ThemeSet::class)->cascadePersist()->build();
        $builder->createOneToOne('edit_theme_set', ThemeSet::class)->build();

        $metadata->addEntityListener(Events::prePersist, BrandListener::class, Events::prePersist);
        $metadata->addEntityListener(Events::preUpdate, BrandListener::class, Events::preUpdate);
        $metadata->addEntityListener(Events::preRemove, BrandListener::class, Events::preRemove);
        $metadata->mapManyToMany(
            [
                'fieldName'    => 'departments',
                'targetEntity' => Department::class,
                'cascade'      => [
                    'persist',
                    'merge',
                ],
                'inversedBy' => 'brands',
                'joinTable'  => [
                    'name'        => 'department_to_brand',
                    'joinColumns' => [
                        0 => [
                            'name'                 => 'brand_id',
                            'referencedColumnName' => 'id',
                            'nullable'             => false,
                            'onDelete'             => 'cascade',
                        ],
                    ],
                    'inverseJoinColumns' => [
                        0 => [
                            'name'                 => 'department_id',
                            'referencedColumnName' => 'id',
                            'nullable'             => false,
                            'onDelete'             => 'cascade',
                        ],
                    ],
                ],
            ]
        );
    }
}

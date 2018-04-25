<?php

namespace Application\DeskPRO\Entity;

use Application\DeskPRO\App;
use Application\DeskPRO\Domain\DomainObject;
use Application\DeskPRO\EntityRepository\Guide as ManualRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use JMS\Serializer\Annotation as JMS;
use Orb\Util\Strings;
use Symfony\Component\Validator\Constraints as Assert;

class Guide extends DomainObject
{
    /**
     * The unique id of the guide.
     *
     * @var int
     * @JMS\Expose()
     * @JMS\Groups("list")
     * @JMS\Type("integer")
     */
    protected $id = null;

    /**
     * Category`s title.
     *
     * @JMS\Expose()
     * @JMS\Type("string")
     * @JMS\Groups("list")
     *
     * @Assert\NotBlank()
     *
     * @var string
     */
    protected $title;

    /**
     * @JMS\Expose()
     * @JMS\Type("string")
     * @JMS\Groups("list")
     *
     * @var string
     */
    protected $slug;

    /**
     * Display order.
     *
     * @JMS\Expose()
     * @JMS\Type("integer")
     * @JMS\Groups("list")
     *
     * @var int
     */
    protected $display_order = 0;

    /**
     * Manual topics in this manual.
     *
     * @JMS\Expose()
     * @JMS\Type("collection<entity<Application\DeskPRO\Entity\Topic>>")
     * @JMS\Groups("details")
     *
     * @var Topic[]|ArrayCollection
     */
    protected $topics;

    /**
     * Usergroups that has access to this category.
     *
     * @JMS\Expose()
     * @JMS\Groups("details")
     * @JMS\Type("collection<entity<Application\DeskPRO\Entity\Usergroup>>")
     *
     * @var \Doctrine\Common\Collections\ArrayCollection
     */
    protected $usergroups;

    /**
     * Brand linked to the category.
     *
     * @JMS\Groups("guides")
     * @JMS\Type("entity<Application\DeskPRO\Entity\Brand>")
     * @JMS\Groups("list")
     *
     * @var Brand
     */
    protected $brand;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param $title
     *
     * @return $this
     */
    public function setTitle($title)
    {
        $this->setModelField('title', $title);

        $this->updateSlug();

        return $this;
    }

    /**
     * @return string
     */
    public function getSlug()
    {
        return $this->slug;
    }

    public function updateSlug()
    {
        $this->slug = Strings::slugifyTitle($this->title);

        $this->setSlug($this->slug);
    }

    /**
     * @param $slug
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
     *
     * @deprecated use getSlug instead
     */
    public function getUrlSlug()
    {
        return $this->id.'-'.Strings::slugifyTitle($this->title);
    }

    /**
     * @return int
     */
    public function getDisplayOrder()
    {
        return $this->display_order;
    }

    /**
     * @param int $display_order
     *
     * @return Guide
     */
    public function setDisplayOrder($display_order)
    {
        $this->setModelField('display_order', $display_order);

        return $this;
    }

    /**
     * @return Topic[]|ArrayCollection
     */
    public function getTopics()
    {
        return $this->topics->filter(function ($topic) {
            /* @var Topic $topic */
            return $topic->getStatus() !== Topic::STATUS_HIDDEN && !$topic->getParent();
        });
    }

    /**
     * @param ArrayCollection $topics
     *
     * @return Guide
     */
    public function setTopics($topics)
    {
        $this->setModelField('topics', $topics);

        return $this;
    }

    /**
     * @return ArrayCollection|Usergroup[]
     */
    public function getUsergroups()
    {
        return $this->usergroups;
    }

    /**
     * @param Usergroup $usergroup
     */
    public function addUsergroup(Usergroup $usergroup)
    {
        if (!$this->usergroups->contains($usergroup)) {
            $this->usergroups->add($usergroup);
        }
    }

    /**
     * Publish controller wants to delete my children but I don't have any.
     *
     * @return array
     */
    public function getChildren()
    {
        return [];
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
    public function setBrand($brand)
    {
        $this->setModelField('brand', $brand);

        return $this;
    }

    /**
     * @JMS\VirtualProperty()
     */
    public function getGuidePdf()
    {
        $filename = DP_DIR.'/attachments/guides/pdf/'.$this->getSlug().'.pdf';
        if (file_exists($filename)) {
            return App::get('router')->generate(
                'guides_pdf',
                ['slug' => $this->getSlug()]);
        }

        return '';
    }

    /**
     * {@inheritdoc}
     */
    public function __toString()
    {
        return $this->getTitle();
    }

    //###########################################################################
    // Doctrine Metadata
    //###########################################################################

    public static function loadMetadata(ClassMetadata $metadata)
    {
        $metadata->setInheritanceType(ClassMetadataInfo::INHERITANCE_TYPE_NONE);
        $metadata->customRepositoryClassName = ManualRepository::class;
        $metadata->setPrimaryTable(['name' => 'guides']);
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
        $metadata->setIdGeneratorType(ClassMetadataInfo::GENERATOR_TYPE_IDENTITY);
        $metadata->mapOneToMany(
            [
                'fieldName'    => 'topics',
                'targetEntity' => Topic::class,
                'mappedBy'     => 'guide',
                'orderBy'      => ['display_order' => 'ASC'],
            ]
        );
        $metadata->mapManyToMany(
            [
                'fieldName'    => 'usergroups',
                'targetEntity' => Usergroup::class,
                'cascade'      => ['persist', 'merge'],
                'joinTable'    => [
                    'name'        => 'guide2usergroup',
                    'schema'      => null,
                    'joinColumns' => [
                        [
                            'name'                 => 'guide_id',
                            'referencedColumnName' => 'id',
                            'nullable'             => true,
                            'onDelete'             => 'cascade',
                            'columnDefinition'     => null,
                        ],
                    ],
                    'inverseJoinColumns' => [
                        [
                            'name'                 => 'usergroup_id',
                            'referencedColumnName' => 'id',
                            'nullable'             => true,
                            'onDelete'             => 'cascade',
                            'columnDefinition'     => null,
                        ],
                    ],
                ],
                'dpApi' => true,
            ]
        );
        $metadata->mapManyToOne(
            [
                'fieldName'    => 'brand',
                'targetEntity' => Brand::class,
                'mappedBy'     => null,
                'inversedBy'   => null,
                'joinColumns'  => [
                    [
                        'name'                 => 'brand_id',
                        'referencedColumnName' => 'id',
                        'onDelete'             => 'set null',
                    ],
                ],
                'dpApi' => true,
            ]
        );
    }
}

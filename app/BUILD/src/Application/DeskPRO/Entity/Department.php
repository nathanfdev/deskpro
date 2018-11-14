<?php

namespace Application\DeskPRO\Entity;

use Application\DeskPRO\App;
use Application\DeskPRO\Domain\DomainObject;
use Application\DeskPRO\Entity\Avatar\AvatarOwner;
use Application\DeskPRO\Entity\Hierarchy\Hierarchical;
use Application\DeskPRO\EntityRepository\Department as DepartmentRepository;
use Application\DeskPRO\Translate\HasPhraseName;
use Application\DeskPRO\Translate\Translate;
use DeskPRO\Bundle\AppBundle\Entity\UserChatQueue;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\ExecutionContextInterface;
use Symfony\Component\Validator\Mapping\ClassMetadata as ValidatorClassMetadata;

/**
 * Departments.
 *
 * @property string                       title
 * @property string                       $user_title
 * @property bool                         $is_tickets_enabled
 * @property bool                         $is_chat_enabled
 * @property int                          $display_order
 * @property Department                   $parent
 * @property Department[]|ArrayCollection $children
 * @property Brand[]|ArrayCollection      $brands
 */
class Department extends DomainObject implements HasPhraseName, AvatarOwner, Hierarchical
{
    /**
     * @var int
     */
    protected $id;

    /**
     * @var Department
     */
    protected $parent = null;

    /**
     * @var ArrayCollection
     */
    protected $children = null;

    /**
     * @var string
     */
    protected $title;

    /**
     * @var string
     */
    protected $user_title = '';

    /**
     * @var bool
     */
    protected $is_tickets_enabled = false;

    /**
     * @var bool
     */
    protected $is_chat_enabled = false;

    /**
     * @var null|array
     */
    protected $_usergroups = null;

    /**
     * @var null|array
     */
    protected $_people = null;

    /**
     * @var int
     */
    protected $display_order = 0;

    /**
     * @var Blob
     */
    protected $avatar;

    /**
     * @var \Doctrine\Common\Collections\ArrayCollection
     */
    protected $brands;

    /**
     * @var DepartmentPermission[]|ArrayCollection
     */
    protected $permissions;

    /**
     * @var UserChatQueue
     */
    protected $chatQueue;

    /**
     * @return Department
     */
    public static function createTicketDepartment()
    {
        $dep                     = new self();
        $dep->is_tickets_enabled = true;
        $dep->is_chat_enabled    = false;

        return $dep;
    }

    /**
     * @return Department
     */
    public static function createChatDepartment()
    {
        $dep                     = new self();
        $dep->is_tickets_enabled = false;
        $dep->is_chat_enabled    = true;

        return $dep;
    }

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->children = new ArrayCollection();
        $this->brands   = new ArrayCollection();
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return int
     */
    public function getDisplayOrder()
    {
        return $this->display_order;
    }

    /**
     * @param $type
     *
     * @return bool
     */
    public function isType($type)
    {
        if ($type == 'tickets' && $this->is_tickets_enabled) {
            return true;
        } elseif ($type == 'chat' && $this->is_chat_enabled) {
            return true;
        }

        return false;
    }

    /**
     * @return string
     */
    public function getRealUserTitle()
    {
        return $this->user_title;
    }

    /**
     * @return string
     */
    public function getUserTitle()
    {
        if ($this->user_title) {
            return $this->user_title;
        }

        return $this->title;
    }

    /**
     * @param $title
     */
    public function setUserTitle($title)
    {
        if (!$title) {
            $title = '';
        }

        $old              = $this->getRealUserTitle();
        $this->user_title = $title;

        if ($title == $old) {
            return;
        }

        $this->_onPropertyChanged('user_title', $old, $title);
    }

    /**
     * @return Department|null
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * An array of all parents in this tree.
     *
     * @return Department[]
     */
    public function getAllParents()
    {
        $parents = [];
        $d       = $this;
        while ($d = $d->getParent()) {
            $parents[] = $d;
        }

        return $parents;
    }

    /**
     * @return int
     */
    public function getParentId()
    {
        if ($this->parent) {
            return $this->parent->getId();
        }

        return 0;
    }

    /**
     * @param Department $parent
     *
     * @return $this
     */
    public function setParent(Department $parent = null)
    {
        $this->setModelField('parent', $parent);

        return $this;
    }

    /**
     * @param $id
     */
    public function setParentId($id)
    {
        if ($id) {
            $this->parent = App::getEntityRepository(self::class)->find($id);
        } else {
            $this->parent = null;
        }
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;

        //TODO: getX should always be the actual values
        return App::getTranslator()->getPhraseObject($this, 'title');
    }

    /**
     * @return string
     */
    public function getRealTitle()
    {
        return $this->title;
    }

    /**
     * @param $title
     */
    public function setRealTitle($title)
    {
        $this->setModelField('title', $title);
    }

    /**
     * Get the 'full' name of this department by prepending the parents name to it.
     *
     * @param string $sep
     *
     * @return string
     */
    public function getFullTitle($sep = null)
    {
        if ($sep === null) {
            $sep = ' > ';
        }

        if (!$this->parent) {
            return $this->getTitle();
        }

        return $this->parent->getTitle().$sep.$this->getTitle();
    }

    /**
     * @param null $sep
     *
     * @return string
     */
    public function getFullUserTitle($sep = null)
    {
        if ($sep === null) {
            $sep = ' > ';
        }

        if (!$this->parent) {
            return $this->getUserTitle();
        }

        return $this->parent->getUserTitle().$sep.$this->getUserTitle();
    }

    /**
     * Add a child department.
     *
     * @param Department $department
     */
    public function addChild(Department $department)
    {
        $department['parent'] = $this;
        $this->children->add($department);
    }

    /**
     * @return array
     */
    public function getChildrenOrdered()
    {
        $children = $this->children->toArray();

        uasort($children, function ($a, $b) {
            if ($a->display_order == $b->display_order) {
                return 0;
            }

            return ($a->display_order < $b->display_order) ? -1 : 1;
        });

        return $children;
    }

    /**
     * Get all children down the entire tree.
     *
     * @return array|Department[]
     */
    public function getAllChildren()
    {
        $children = [];
        $iterator = function (Department $department) use (&$children, &$iterator) {
            foreach ($department->getChildren() as $child) {
                $children[] = $child;
                $iterator($child);
            }
        };

        $iterator($this);

        return $children;
    }

    /**
     * @return \Doctrine\Common\Collections\ArrayCollection|null
     */
    public function getChildren()
    {
        return $this->children;
    }

    /**
     * @return bool
     */
    public function isLeaf()
    {
        return $this->children->count() === 0;
    }

    /**
     * {@inheritdoc}
     */
    public function getPhraseName($property)
    {
        if (!$property) {
            $property = 'title';
        }

        $phrase_name = 'obj_department.'.$this->id.'_'.$property;

        if ($property == 'user') {
            return [
                'obj_department.'.$this->id.'_user',
                'obj_department.'.$this->id.'_title',
            ];
        }

        return $phrase_name;
    }

    /**
     * {@inheritdoc}
     */
    public function getPhraseDefault($property, Translate $translate)
    {
        if ($property == 'full') {
            return $this->getRealTitle();
        }

        if ($property == 'user' && $this->user_title) {
            return $this->user_title;
        }

        return $this->title;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->getFullTitle();
    }

    /**
     * @return Blob
     */
    public function getAvatarBlob()
    {
        return $this->avatar;
    }

    /**
     * @param bool $isChatEnabled
     *
     * @return $this
     */
    public function setIsChatEnabled($isChatEnabled)
    {
        $this->setModelField('is_chat_enabled', $isChatEnabled);

        return $this;
    }

    /**
     * @param bool $isTicketsEnabled
     *
     * @return $this
     */
    public function setIsTicketsEnabled($isTicketsEnabled)
    {
        $this->setModelField('is_tickets_enabled', $isTicketsEnabled);

        return $this;
    }

    public function isChatEnabled()
    {
        return $this->is_chat_enabled;
    }

    public function isTicketsEnabled()
    {
        return $this->is_tickets_enabled;
    }

    /**
     * @return Brand[]|ArrayCollection
     */
    public function getBrands()
    {
        return $this->brands;
    }

    /**
     * @param array|ArrayCollection $brands
     */
    public function setBrands($brands)
    {
        $this->brands = $brands;
        foreach ($this->brands as $brand) {
            $brand->addDepartment($this);
        }

        $this->_onPropertyChanged('brands', null, $this->brands);
    }

    /**
     * @param Brand $searchBrand
     *
     * @return bool
     */
    public function hasBrand(Brand $searchBrand)
    {
        return $this->brands->contains($searchBrand);
    }

    /**
     * @param Brand $brand
     *
     * @return $this
     */
    public function addBrand(Brand $brand)
    {
        if (!$this->brands->contains($brand)) {
            $this->brands->add($brand);
            $brand->addDepartment($this);

            $this->_onPropertyChanged('brands', null, $this->brands);
        }

        return $this;
    }

    /**
     * @param Brand $brand
     *
     * @return $this
     */
    public function removeBrand(Brand $brand)
    {
        $this->brands->removeElement($brand);
        $brand->removeDepartment($this);
        $this->_onPropertyChanged('brands', null, $this->brands);

        return $this;
    }

    /**
     * @return UserChatQueue
     */
    public function getChatQueue()
    {
        return $this->chatQueue;
    }

    /**
     * @param UserChatQueue $chatQueue
     *
     * @return $this
     */
    public function setChatQueue(UserChatQueue $chatQueue = null)
    {
        $this->setModelField('chatQueue', $chatQueue);

        return $this;
    }

    //###########################################################################
    // Validation Metadata
    //###########################################################################

    public function _validateParent(ExecutionContextInterface $context)
    {
        if (!$this->parent) {
            return;
        }

        if ($this->parent == $this) {
            $context->addViolationAt('parent', '[ParentNotSelf] Parent cannot be set to self');
        }
    }

    public static function loadValidatorMetadata(ValidatorClassMetadata $metadata)
    {
        $metadata->addPropertyConstraint('title', new NotBlank());
        $metadata->addConstraint(new Callback([
            'methods' => ['_validateParent'],
        ]));
    }

    /**
     * {@inheritdoc}
     */
    public function toApiData($primary = true, $deep = true, array $visited = [])
    {
        $data               = parent::toApiData($primary, $deep, $visited);
        $data['user_title'] = $this->getRealUserTitle();
        if (true || $deep) {
            $data['brands'] = [];
            foreach ($this->brands as $brand) {
                $data['brands'][] = $brand->getId();
            }
        }

        if ($this->parent) {
            $data['title_full']       = $this->parent->title.' > '.$this->title;
            $data['parent_id']        = $this->parent->getId();
            $data['parent_ids']       = [$this->parent->getId()];
            $data['title_parts']      = [$this->parent->title, $this->title];
            $data['user_title_parts'] = [$this->parent->getUserTitle(), $this->getUserTitle()];
            $data['has_children']     = false;
        } else {
            $data['title_full']       = $this->title;
            $data['parent_id']        = null;
            $data['parent_ids']       = [];
            $data['title_parts']      = [$this->title];
            $data['user_title_parts'] = [$this->getUserTitle()];
            $data['has_children']     = count($this->children) != 0;
        }

        if ($this->is_chat_enabled) {
            $data['chat_queue_id'] = $this->chatQueue ? $this->chatQueue->getId() : null;
        }

        return $data;
    }

    public function hasAvatar()
    {
        return $this->avatar && $this->avatar->isImage();
    }

    public function getAvatarUrl($size = 50)
    {
        if (!$this->hasAvatar()) {
            return;
        }

        return $this->avatar->getThumbnailUrl($size);
    }

    //###########################################################################
    // Doctrine Metadata
    //###########################################################################

    public static function loadMetadata(ClassMetadata $metadata)
    {
        $metadata->setInheritanceType(ClassMetadataInfo::INHERITANCE_TYPE_NONE);
        $metadata->setChangeTrackingPolicy(ClassMetadataInfo::CHANGETRACKING_NOTIFY);
        $metadata->customRepositoryClassName = DepartmentRepository::class;
        $metadata->setPrimaryTable(['name' => 'departments']);

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
                'fieldName'  => 'user_title',
                'type'       => 'string',
                'length'     => 255,
                'precision'  => 0,
                'scale'      => 0,
                'nullable'   => false,
                'columnName' => 'user_title',
            ]
        );
        $metadata->mapField(
            [
                'fieldName'  => 'is_tickets_enabled',
                'type'       => 'boolean',
                'precision'  => 0,
                'scale'      => 0,
                'nullable'   => false,
                'columnName' => 'is_tickets_enabled',
            ]
        );
        $metadata->mapField(
            [
                'fieldName'  => 'is_chat_enabled',
                'type'       => 'boolean',
                'precision'  => 0,
                'scale'      => 0,
                'nullable'   => false,
                'columnName' => 'is_chat_enabled',
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
        $metadata->mapManyToOne(
            [
                'fieldName'    => 'parent',
                'targetEntity' => self::class,
                'mappedBy'     => null,
                'inversedBy'   => 'children',
                'fetch'        => ClassMetadataInfo::FETCH_EAGER,
                'joinColumns'  => [
                    0 => [
                        'name'                 => 'parent_id',
                        'referencedColumnName' => 'id',
                        'nullable'             => true,
                        'onDelete'             => 'cascade',
                        'columnDefinition'     => null,
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
                'indexBy'      => 'id',
            ]
        );

        $metadata->mapOneToMany(
            [
                'fieldName'    => 'permissions',
                'targetEntity' => DepartmentPermission::class,
                'mappedBy'     => 'department',
            ]
        );

        $metadata->mapManyToOne([
            'fieldName'    => 'avatar',
            'targetEntity' => Blob::class,
            'mappedBy'     => null,
            'inversedBy'   => null,
            'fetch'        => ClassMetadataInfo::FETCH_EAGER,
            'joinColumns'  => [
                0 => [
                    'name'                 => 'avatar_blob_id',
                    'referencedColumnName' => 'id',
                    'nullable'             => true,
                    'onDelete'             => 'cascade',
                    'columnDefinition'     => null,
                ],
            ],
            'dpApi' => true,
        ]);

        $metadata->mapManyToMany(
            [
                'fieldName'    => 'brands',
                'targetEntity' => Brand::class,
                'cascade'      => [
                    'persist',
                    'merge',
                ],
                'mappedBy'  => 'departments',
                'joinTable' => [
                    'name'        => 'department_to_brand',
                    'joinColumns' => [
                        0 => [
                            'name'                 => 'department_id',
                            'referencedColumnName' => 'id',
                            'nullable'             => false,
                            'onDelete'             => 'cascade',
                        ],
                    ],
                    'inverseJoinColumns' => [
                        0 => [
                            'name'                 => 'brand_id',
                            'referencedColumnName' => 'id',
                            'nullable'             => false,
                            'onDelete'             => 'cascade',
                        ],
                    ],
                ],
            ]
        );
        $metadata->mapManyToOne(
            [
                'fieldName'    => 'chatQueue',
                'targetEntity' => UserChatQueue::class,
                'mappedBy'     => null,
                'inversedBy'   => 'departments',
                'fetch'        => ClassMetadataInfo::FETCH_LAZY,
                'joinColumns'  => [
                    0 => [
                        'name'                 => 'chat_queue_id',
                        'referencedColumnName' => 'id',
                        'nullable'             => true,
                        'onDelete'             => 'set null',
                        'columnDefinition'     => null,
                    ],
                ],
            ]
        );
    }
}

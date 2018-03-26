<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\Entity;

use Application\DeskPRO\App;
use Application\DeskPRO\Domain\DomainObject;
use Application\DeskPRO\Entity\Hierarchy\Hierarchical;
use Application\DeskPRO\Translate\HasPhraseName;
use Application\DeskPRO\Translate\Translate;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use JMS\Serializer\Annotation as JMS;

/**
 * Ticket categories.
 *
 * @JMS\ExclusionPolicy("all")
 */
class TicketCategory extends DomainObject implements HasPhraseName, Hierarchical
{
    /**
     * The unique ID.
     *
     * @JMS\Expose()
     * @JMS\Type("integer")
     *
     * @var int
     */
    protected $id = null;

    /**
     * This category parent.
     *
     * @JMS\Expose()
     * @JMS\Type("entity<Application\DeskPRO\Entity\TicketCategory>")
     *
     * @var TicketCategory
     */
    protected $parent = null;

    /**
     * @var \Doctrine\Common\Collections\ArrayCollection
     */
    protected $children = null;

    /**
     * Category title.
     *
     * @JMS\Expose()
     * @JMS\Type("string")
     *
     * @var string
     */
    protected $title;

    /**
     * Obviously it's display order for lists.
     *
     * @JMS\Expose()
     * @JMS\Type("integer")
     *
     * @var int
     */
    protected $display_order = 0;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->children = new ArrayCollection();
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    public function getParentId()
    {
        if ($this->parent) {
            return $this->parent['id'];
        }

        return 0;
    }

    public function setParentId($id)
    {
        if ($id) {
            $this->setModelField('parent', App::getEntityRepository('DeskPRO:Department')->find($id));
        } else {
            $this->setModelField('parent', null);
        }
    }

    /**
     * @return TicketCategory
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return App::getTranslator()->getPhraseObject($this, 'title');
    }

    /**
     * Set ticket category title.
     *
     * @param string $title
     *
     * @return $this
     */
    public function setRealTitle($title)
    {
        $this->setModelField('title', $title);

        return $this;
    }

    /**
     * @return string
     */
    public function getRealTitle()
    {
        return $this->title;
    }

    /**
     * Get the 'full' name.
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
     * Add a child department.
     *
     * @param TicketCategory $category
     */
    public function addChild(TicketCategory $category)
    {
        $category['parent'] = $this;
        $this->children->add($category);
    }

    /**
     * Get children.
     *
     * @return \Doctrine\Common\Collections\ArrayCollection|TicketCategory[]
     */
    public function getChildren()
    {
        if ($this->parent) {
            // empty collection
            return new ArrayCollection();
        }

        return $this->children;
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
     * @return array
     */
    public function getAllChildren()
    {
        return $this->getChildren();
    }

    /**
     * {@inheritdoc}
     */
    public function getPhraseName($property)
    {
        if (!$property) {
            $property = 'title';
        }
        $phrase_name = 'obj_ticketcategory.'.$this->id.'_'.$property;

        return $phrase_name;
    }

    /**
     * {@inheritdoc}
     */
    public function getPhraseDefault($property, Translate $translate)
    {
        if ($property == 'full') {
            return $this->getFullTitle();
        }

        return $this->title;
    }

    /**
     * @return int
     */
    public function getDisplayOrder()
    {
        return $this->display_order;
    }

    public function __toString()
    {
        return $this->getFullTitle();
    }

    /**
     * {@inheritdoc}
     */
    public function toApiData($primary = true, $deep = true, array $visited = [])
    {
        $data = parent::toApiData($primary, $deep, $visited);

        if ($this->parent) {
            $data['parent_id'] = $this->parent->getId();
        } else {
            $data['parent_id'] = null;
        }

        return $data;
    }

    //###########################################################################
    // Doctrine Metadata
    //###########################################################################

    public static function loadMetadata(ClassMetadata $metadata)
    {
        $metadata->setInheritanceType(ClassMetadataInfo::INHERITANCE_TYPE_NONE);
        $metadata->customRepositoryClassName = 'Application\DeskPRO\EntityRepository\TicketCategory';
        $metadata->setPrimaryTable(['name' => 'ticket_categories']);
        $metadata->setChangeTrackingPolicy(ClassMetadataInfo::CHANGETRACKING_NOTIFY);
        $metadata->mapField([
            'fieldName'  => 'id',
            'type'       => 'integer',
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => false,
            'columnName' => 'id',
            'id'         => true,
        ]);
        $metadata->mapField([
            'fieldName'  => 'title',
            'type'       => 'string',
            'length'     => 255,
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => false,
            'columnName' => 'title',
        ]);
        $metadata->mapField([
            'fieldName'  => 'display_order',
            'type'       => 'integer',
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => false,
            'columnName' => 'display_order',
        ]);
        $metadata->setIdGeneratorType(ClassMetadataInfo::GENERATOR_TYPE_IDENTITY);
        $metadata->mapManyToOne([
            'fieldName'    => 'parent',
            'targetEntity' => 'Application\\DeskPRO\\Entity\\TicketCategory',
            'mappedBy'     => null,
            'inversedBy'   => 'children',
            'joinColumns'  => [
                [
                    'name'                 => 'parent_id',
                    'referencedColumnName' => 'id',
                    'nullable'             => true,
                    'onDelete'             => 'cascade',
                    'columnDefinition'     => null,
                ],
            ],
        ]);
        $metadata->mapOneToMany([
            'fieldName'    => 'children',
            'targetEntity' => 'Application\\DeskPRO\\Entity\\TicketCategory',
            'mappedBy'     => 'parent',
            'orderBy'      => ['display_order' => 'ASC'],
        ]);
    }
}

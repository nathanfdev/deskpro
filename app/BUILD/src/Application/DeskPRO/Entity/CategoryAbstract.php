<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2016, DeskPRO Ltd.
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

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\Entity;

use Application\DeskPRO\Translate\HasPhraseName;
use Application\DeskPRO\Translate\Translate;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use JMS\Serializer\Annotation as JMS;
use Orb\Util\Strings;
use Orb\Util\Util;

/**
 * Basic hierarchicial category entity.
 *
 * @JMS\ExclusionPolicy("all")
 */
class CategoryAbstract extends \Application\DeskPRO\Domain\DomainObject implements HasPhraseName
{
    /**
     * The unique id of the category.
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
     * @var string
     */
    protected $title;

    /**
     * @var string
     */
    protected $slug;

    /**
     * Display order.
     *
     * @JMS\Expose()
     * @JMS\Type("integer")
     * @JMS\Groups("product")
     *
     * @var int
     */
    protected $display_order = 0;

    // IMPLEMENT IN CHILDREN : Limitation of doctrine mapping, you have to map these with the correct targets
    ///**
    // */
    //protected $parent;
    //
    ///**
    // */
    //protected $children;

    // IMPLEMENT IN CHILDREN (optional)
    ///**
    // * @var Doctrine\Common\Collections\ArrayCollection
    // */
    //protected $usergroups;

    /**
     */
    protected $depth = 0;

    /**
     *  */
    protected $root;

    /**
     * Local cache of some structure info with this category.
     *
     * @var array()
     */
    protected $_structure = [];

    /**
     * @var \Application\DeskPRO\Publish\Structure
     */
    public $structure_helper;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param CategoryAbstract|null $category
     *
     * @return $this
     */
    public function setParent(CategoryAbstract $category = null)
    {
        if ($category && $category->getId() && $this->getId() && $category->getId() == $this->getId()) {
            throw new \InvalidArgumentException('Cannot set parent to self');
        }

        $this->setModelField('parent', $category);

        if ($category) {
            $this->setModelField('root', $category->root ? $category->root : $category->id);
            $this->setModelField('depth', $category->depth + 1);
        } else {
            $this->setModelField('root', null);
            $this->setModelField('depth', 0);
        }

        return $this;
    }

    /**
     * @return int
     */
    public function getParentId()
    {
        if ($this->parent) {
            return $this->parent->id;
        }

        return 0;
    }

    /**
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
    public function getTitle()
    {
        return $this->title;
    }

    public function getRealTitle()
    {
        return $this->title;
    }

    public function updateSlug()
    {
        $this->slug = Strings::slugifyTitle($this->title);
        $this->setModelField('slug', $this->slug);
    }

    public function setTitle($title)
    {
        $this->setModelField('title', $title);

        $this->updateSlug();
    }

    /**
     * Get an array of titles from parents down to this.
     *
     * @return array
     */
    public function getTitleParts()
    {
        $titles = [];
        foreach ($this->getTreeParents() as $p) {
            $titles[] = $p['title'];
        }
        $titles[] = $this->getTitle();

        return $titles;
    }

    /**
     * Get the full display title for the category with all parents parts, separated
     * by $sep. Example: Category > Subcategory.
     *
     * @param string $sep
     *
     * @return string
     */
    public function getFullTitle($sep = ' > ')
    {
        return implode($sep, $this->getTitleParts());
    }

    /**
     * Gets all parents in the tree, in order (left to right, aka, top to bottom).
     *
     * @return CategoryAbstract[]
     */
    public function getTreeParents()
    {
        if (isset($this->_structure['all_parents'])) {
            return $this->_structure['all_parents'];
        }

        $this->_structure['all_parents'] = [];
        $cat                             = $this;
        while ($cat->getParent()) {
            $this->_structure['all_parents'][$cat->getParent()->id] = $cat->getParent();
            $cat                                                    = $cat->parent;
        }

        $this->_structure['all_parents'] = array_reverse($this->_structure['all_parents'], true);

        return $this->_structure['all_parents'];
    }

    /**
     * Get all IDs of this tree, from this node and downwards.
     *
     * @param bool $including_this Include this nodes ID in the array of ids
     *
     * @return array
     */
    public function getTreeIds($including_this = true)
    {
        if (!isset($this->_structure['all_child_ids'])) {
            $all_ids = [];
            $r       = function (CategoryAbstract $cat) use (&$r, &$all_ids) {
                if ($children = $cat->getChildren()) {
                    foreach ($cat->getChildren() as $c) {
                        $all_ids[] = $c->id;
                        if ($c->getChildren()) {
                            $r($c);
                        }
                    }
                }
            };
            $r($this);

            $this->_structure['all_child_ids'] = $all_ids;
        }

        $ids = $this->_structure['all_child_ids'];
        if ($including_this) {
            array_unshift($ids, $this->id);
        }

        return $ids;
    }

    /**
     * @return ArrayCollection|CategoryAbstract[]
     */
    public function getChildren()
    {
        if ($this->structure_helper) {
            //return $this->structure_helper->getCategoryHelperForCategory($this)->getChildren($this);
        }

        return $this->children ?: new ArrayCollection();
    }

    /**
     * @return int|mixed
     */
    public function getParent()
    {
        if ($this->structure_helper) {
            return $this->structure_helper->getCategoryHelperForCategory($this)->getParent($this);
        }

        return $this->parent;
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
     * Return a unique ID that we can use to look up translations for this object.
     *
     * @param string $property If supplied, the property on the object we want to translate.
     *
     * @return string
     */
    public function getPhraseName($property = null, Translate $translate)
    {
        if (!$property) {
            $property = 'title';
        }
        $name        = strtolower(Util::getBaseClassname($this));
        $phrase_name = 'obj_'.$name.'.'.$this->id.'_'.$property;

        return $phrase_name;
    }

    /**
     * Get the default value phrase for the object.
     *
     * @param string $property If supplied, the property on the object we want to translate.
     *
     * @return string
     */
    public function getPhraseDefault($property = null, Translate $translate)
    {
        if ($property == 'full') {
            return $this->getFullTitle();
        }

        return $this->title;
    }

    /**
     * @return string
     */
    public function getSelectTitle()
    {
        if ($this->depth) {
            return str_repeat('--', $this->depth).' '.$this->title;
        } else {
            return $this->title;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function __toString()
    {
        return $this->getFullTitle();
    }

    ############################################################################
    # Doctrine Metadata
    ############################################################################

    public static function loadMetadata(ClassMetadata $metadata)
    {
        $metadata->isMappedSuperclass = true;
        $metadata->setInheritanceType(ClassMetadataInfo::INHERITANCE_TYPE_NONE);
        $metadata->setPrimaryTable(['name' => 'CategoryAbstract']);
        $metadata->setChangeTrackingPolicy(ClassMetadataInfo::CHANGETRACKING_NOTIFY);
    }

    /**
     * @return string
     */
    public function getSlug()
    {
        return $this->slug;
    }

    /**
     * @return int
     */
    public function getDisplayOrder()
    {
        return $this->display_order;
    }
}

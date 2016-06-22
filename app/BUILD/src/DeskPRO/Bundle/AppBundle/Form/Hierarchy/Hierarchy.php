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
 */

namespace DeskPRO\Bundle\AppBundle\Form\Hierarchy;

use DeskPRO\Bundle\AppBundle\Form\ChoiceList\HierarchyChoiceList;
use DeskPRO\Component\Hierarchy\Hierarchy as BaseHierarchy;
use DeskPRO\Component\Hierarchy\HierarchyFormatterInterface;
use DeskPRO\Component\Hierarchy\HierarchyNode as BaseNode;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Represents a hierarchy.
 *
 * getChoiceList can be used directly in a form (choice type) and will use the hierarchy formatter to render options
 * and the ID of the entity (by default) as the value.
 */
class Hierarchy extends BaseHierarchy
{
    /**
     * @var bool
     */
    private $leaf_selections_only;

    /**
     * Constructor.
     *
     * @param array                            $root_nodes
     * @param HierarchyFormatterInterface|null $formatter
     * @param string                           $node_id_path
     */
    public function __construct(array $root_nodes, HierarchyFormatterInterface $formatter = null, $node_id_path = null)
    {
        parent::__construct($root_nodes, $formatter, $node_id_path);
        $this->leaf_selections_only = false;
    }

    /**
     * @return HierarchyNode[]
     */
    public function getFlattened()
    {
        $collection = new ArrayCollection();
        foreach ($this->getRootNodes() as $root_node) {
            self::flatten($root_node, $collection);
        }

        return $collection;
    }

    /**
     * @param HierarchyNode   $node
     * @param ArrayCollection $append_to_collection
     *
     * @return ArrayCollection|HierarchyNode[]
     */
    public static function flatten(HierarchyNode $node, ArrayCollection $append_to_collection = null)
    {
        if (!$append_to_collection) {
            $append_to_collection = new ArrayCollection();
        }

        $append_to_collection->add($node);

        foreach ($node as $child) {
            self::flatten($child, $append_to_collection);
        }

        return $append_to_collection;
    }

    /**
     * @return HierarchyChoiceList
     */
    public function getChoiceList()
    {
        $choices = [];
        $labels  = [];

        if (!$this->leaf_selections_only) {
            /** @var HierarchyNode $node */
            foreach ($this->getFlattened() as $node) {
                $label = (string) $node;
                $key   = $this->getNodeId($node);

                $choices[$key] = $node;
                $labels[$key]  = $label;
            }

            return new HierarchyChoiceList($choices, $labels);
        }

        /** @var HierarchyNode $node */
        foreach ($this as $node) {
            $choices[$this->getNodeId($node)] = $node;
            $labels[$this->getNodeId($node)]  = (string) $node;
            foreach ($node->getChoices() as $id => $nid) {
                $choices[$id] = $nid;
            }
            foreach ($node->getLabels() as $id => $nl) {
                $labels[$id] = $nl;
            }
        }

        return new HierarchyChoiceList($choices, $labels);
    }

    public function markOnlyLeafSelections()
    {
        $this->leaf_selections_only = true;
    }

    /**
     * Some nodes are parents and not selectable, count only leaf nodes.
     *
     * @return int
     */
    public function countSelectable()
    {
        return $this->countTree(true);
    }

    /**
     * Similar to countSelectable(), this method will return this first selectable (the first that would appear in a select box, for example).
     *
     * @return mixed
     */
    public function getFirstSelectable()
    {
        foreach ($this->root_nodes as $node) {
            return $this->findSelectable($node)->getData(); // find the first leaf of the first root node
        }

        return false;
    }

    /**
     * @param BaseNode $node
     *
     * @return BaseNode
     */
    public function findSelectable(BaseNode $node)
    {
        if ($node->isLeaf()) {
            return $node;
        }

        $children    = $node->getChildren();
        $first_child = $children[0];

        return $this->findSelectable($first_child);
    }

    /**
     * Counts all nodes in the tree.
     *
     * @param bool $only_count_left_nodes
     *
     * @return int
     */
    public function countTree($only_count_left_nodes = false)
    {
        $count = 0;

        foreach ($this->root_nodes as $root) {
            $count += $root->countTree($only_count_left_nodes);
        }

        return $count;
    }
}

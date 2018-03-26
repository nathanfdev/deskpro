<?php

namespace DeskPRO\Bundle\AppBundle\Form\Hierarchy;

use DeskPRO\Component\Hierarchy\Hierarchy as BaseHierarchy;
use DeskPRO\Component\Hierarchy\HierarchyFormatterInterface;
use DeskPRO\Component\Hierarchy\HierarchyNode;
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
     * @return ArrayCollection
     */
    public function getFlattened()
    {
        $collection = new ArrayCollection();
        foreach ($this->getRootNodes() as $root_node) {
            $this->flatten($root_node, $collection);
        }

        return $collection;
    }

    /**
     * @param HierarchyNode   $node
     * @param ArrayCollection $collection
     *
     * @return ArrayCollection
     */
    public function flatten(HierarchyNode $node, ArrayCollection $collection)
    {
        $collection->add($node);

        foreach ($node as $child) {
            $this->flatten($child, $collection);
        }

        return $collection;
    }

    /**
     * @return HierarchyChoiceLoader
     */
    public function getChoiceLoader()
    {
        return new HierarchyChoiceLoader($this);
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
     * @param HierarchyNode $node
     *
     * @return HierarchyNode
     */
    public function findSelectable(HierarchyNode $node)
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

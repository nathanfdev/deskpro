<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Component\Hierarchy;

class HierarchyNode implements \IteratorAggregate, \Countable
{
    /**
     * @var Hierarchy
     */
    protected $hierarchy;

    /**
     * @var int
     */
    protected $depth;

    /**
     * @var int
     */
    protected $order;

    /**
     * @var mixed
     */
    protected $data;

    /**
     * @var HierarchyNode[]
     */
    protected $children;

    /**
     * @var HierarchyNode
     */
    protected $parent;

    /**
     * @param int   $depth the depth into the hierarchy that this exists
     * @param int   $order the order amoung this depth (higher is top of list)
     * @param mixed $data  any arbitrary data stored at this location in the hierarchy
     */
    public function __construct($data, $depth = 0, $order = 0)
    {
        $this->depth    = $depth;
        $this->order    = $order;
        $this->data     = $data;
        $this->children = [];
    }

    public function getId()
    {
        return $this->hierarchy->getNodeId($this);
    }

    public function __toString()
    {
        return $this->hierarchy->getFormatter()->format($this);
    }

    public function addChild(HierarchyNode $node)
    {
        $node->setParent($this);
        $this->children[] = $node;
        if ($this->hierarchy) {
            $this->hierarchy->addNode($node);
        }

        // bug in php will throw an exception for modifying arrays in some versions of php during usort
        @usort($this->children, function ($node1, $node2) {
            $a = $node2->getOrder();
            $b = $node1->getOrder();

            if ($a == $b) {
                return 0;
            }

            return ($a < $b) ? -1 : 1;
        });
    }

    public function isLeaf()
    {
        return count($this->children) === 0;
    }

    public function getChildren()
    {
        return $this->children;
    }

    /**
     * @param Hierarchy $hierarchy
     */
    public function setHierarchy(Hierarchy $hierarchy)
    {
        $this->hierarchy = $hierarchy;
    }

    /**
     * @return Hierarchy
     */
    public function getHierarchy()
    {
        return $this->hierarchy;
    }

    /**
     * @return HierarchyNode
     */
    public function getRoot()
    {
        return $this->hierarchy->getRootNode();
    }

    /**
     * @return int
     */
    public function getDepth()
    {
        return $this->depth;
    }

    /**
     * @return int
     */
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
    }

    public function getIterator()
    {
        return new \ArrayIterator($this->children);
    }

    public function count()
    {
        return count($this->children);
    }

    private function setParent(HierarchyNode $node)
    {
        $this->parent = $node;
    }

    /**
     * @return HierarchyNode
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * Ordered list of all parents of this node, starting with root.
     *
     * @return HierarchyNode[]
     */
    public function getParents()
    {
        $parents = [];

        if ($parent = $this->getParent()) {
            $parents[] = $parent;

            $grant_parents = $parent->getParents();
            foreach ($grant_parents as $grant_parent) {
                $parents[] = $grant_parent;
            }
        }

        return array_reverse($parents);
    }

    public function countTree($only_count_left_nodes = false)
    {
        if ($only_count_left_nodes && $this->count() > 0) {
            $count = 0; // dont count this node if we only want the leaf nodes
        } else {
            $count = 1;
        }

        foreach ($this->children as $child) {
            $count += $child->countTree($only_count_left_nodes);
        }

        return $count;
    }
}

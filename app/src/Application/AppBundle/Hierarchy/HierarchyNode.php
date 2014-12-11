<?php
/**************************************************************************\
 * | DeskPRO (r) has been developed by DeskPRO Ltd. http://www.deskpro.com/   |
 * | a British company located in London, England.                            |
 * |                                                                          |
 * | All source code and content Copyright (c) 2012, DeskPRO Ltd.             |
 * |                                                                          |
 * | The license agreement under which this software is released              |
 * | can be found at http://www.deskpro.com/license                           |
 * |                                                                          |
 * | By using this software, you acknowledge having read the license          |
 * | and agree to be bound thereby.                                           |
 * |                                                                          |
 * | Please note that DeskPRO is not free software. We release the full       |
 * | source code for our software because we trust our users to pay us for    |
 * | the huge investment in time and energy that has gone into both creating  |
 * | this software and supporting our customers. By providing the source code |
 * | we preserve our customers' ability to modify, audit and learn from our   |
 * | work. We have been developing DeskPRO since 2001, please help us make it |
 * | another decade.                                                          |
 * |                                                                          |
 * | Like the work you see? Think you could make it better? We are always     |
 * | looking for great developers to join us: http://www.deskpro.com/jobs/    |
 * |                                                                          |
 * | ~ Thanks, Everyone at Team DeskPRO                                       |
 * \**************************************************************************/

/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage
 */

namespace Application\AppBundle\Hierarchy;


use Application\FormBundle\Hierarchy\Hierarchy;

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
     * @param int   $depth the depth into the hierarchy that this exists
     * @param int   $order the order amoung this depth (higher is top of list)
     * @param mixed $data  any arbitrary data stored at this location in the hierarchy
     */
    public function __construct($data, $depth = 0, $order = 0)
    {
        $this->depth = $depth;
        $this->order = $order;
        $this->data = $data;
        $this->children = array();
    }

    public function __toString()
    {
        return $this->hierarchy->getFormatter()->format($this);
    }

    public function addChild(HierarchyNode $node)
    {
        $node->setHierarchy($this->hierarchy);
        $this->children[] = $node;
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

    /**
     * Able to recursively find a child with a given ID (ID as defined by the hierarchy property accessor)
     *
     * @param $node_id
     * @param bool $recursive
     * @return HierarchyNode|null
     */
    public function findChildById($node_id, $recursive = false)
    {
        foreach ($this->children as $child) {
            if ($node_id == $this->hierarchy->getNodeId($child)) {
                return $child;
            }
        }

        if ($recursive) {
            foreach ($this->children as $child) {
                if ($response = $child->getChildById($node_id)) {
                    return $response;
                }
            }
        }

        return null;
    }
}

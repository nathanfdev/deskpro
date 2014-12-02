<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. http://www.deskpro.com/   |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2012, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at http://www.deskpro.com/license                           |
|                                                                          |
| By using this software, you acknowledge having read the license          |
| and agree to be bound thereby.                                           |
|                                                                          |
| Please note that DeskPRO is not free software. We release the full       |
| source code for our software because we trust our users to pay us for    |
| the huge investment in time and energy that has gone into both creating  |
| this software and supporting our customers. By providing the source code |
| we preserve our customers' ability to modify, audit and learn from our   |
| work. We have been developing DeskPRO since 2001, please help us make it |
| another decade.                                                          |
|                                                                          |
| Like the work you see? Think you could make it better? We are always     |
| looking for great developers to join us: http://www.deskpro.com/jobs/    |
|                                                                          |
| ~ Thanks, Everyone at Team DeskPRO                                       |
\**************************************************************************/

/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage
 */

namespace Application\FormBundle\Heirarchy;

/**
 * A HeirarchyNode is iteratable, and countable, because each can have an arbitray number of children.
 */
class HeirarchyNode implements \IteratorAggregate, \Countable
{
    /**
     * @var Heirarchy
     */
    private $heirarchy;

    /**
     * @var int
     */
    private $depth;

    /**
     * @var int
     */
    private $order;

    /**
     * @var mixed
     */
    private $data;

    /**
     * @var HeirarchyNode[]
     */
    private $children;

    /**
     * @param int   $depth the depth into the heirarchy that this exists
     * @param int   $order the order amoung this depth (higher is top of list)
     * @param mixed $data  any arbitrary data stored at this location in the heirarchy
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
        return $this->heirarchy->getFormatter()->format($this);
    }

    public function addChild(HeirarchyNode $node)
    {
        $node->setHeirarchy($this->heirarchy);
        $this->children[] = $node;
    }

    /**
     * @param Heirarchy $heirarchy
     */
    public function setHeirarchy(Heirarchy $heirarchy)
    {
        $this->heirarchy = $heirarchy;
    }

    /**
     * @return Heirarchy
     */
    public function getHeirarchy()
    {
        return $this->heirarchy;
    }

    /**
     * @return HeirarchyNode
     */
    public function getRoot()
    {
        return $this->heirarchy->getRootNode();
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
     * Recursively get a choices array for a form ChoiceList (only leaf values can be selected, the others are opt groups)
     *
     * @return array
     */
    public function getChoices()
    {
        $choices = array();

        /** @var HeirarchyNode $node */
        foreach ($this as $node) {
            if (count($node)) {
                $choices[(string)$node] = $node->getChoices();
            } else {
                $nodeId = $this->heirarchy->getNodeId($node);
                $choices[$nodeId] = $nodeId;
            }
        }

        return $choices;
    }

    /**
     * Recursively get a labels array for a form ChoiceList (only leaf values can be selected, the others are opt groups)
     *
     * @return array
     */
    public function getLabels()
    {
        $labels = array();

        /** @var HeirarchyNode $node */
        foreach ($this as $node) {
            if (count($node)) {
                $labels[(string)$node] = $node->getLabels();
            } else {
                $nodeId = $this->heirarchy->getNodeId($node);
                $labels[$nodeId] = (string) $node;
            }
        }

        return $labels;
    }
}
 
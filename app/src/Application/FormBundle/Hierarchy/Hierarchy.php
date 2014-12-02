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

namespace Application\FormBundle\Hierarchy;

use Application\FormBundle\Form\ChoiceList\HierarchyChoiceList;
use Application\FormBundle\Hierarchy\Formatter\FlatListFormatter;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Traversable;

/**
 * Represents a hierarchy.
 *
 * getChoiceList can be used directly in a form (choice type) and will use the hierarchy formatter to render options
 * and the ID of the entity (by default) as the value.
 */
class Hierarchy implements \Countable, \IteratorAggregate
{
    /**
     * @var HierarchyFormatterInterface
     */
    private $formatter;

    /**
     * @var HierarchyNode[]
     */
    private $root_nodes;

    /*
     * @var string|null
     */
    private $node_id_path;

    /**
     * @var \Symfony\Component\PropertyAccess\PropertyAccessor
     */
    private $accessor;

    /**
     * @var bool
     */
    private $leaf_selections_only;

    /**
     * @param HierarchyNode[]             $root_nodes
     * @param HierarchyFormatterInterface $formatter
     * @param string|null $node_id_path
     */
    public function __construct(array $root_nodes, HierarchyFormatterInterface $formatter = null, $node_id_path = null)
    {
        $this->formatter = $formatter ?: new FlatListFormatter();
        $this->root_nodes = $root_nodes;
        foreach ($root_nodes as $root_node) {
            $root_node->setHierarchy($this);
        }
        $this->node_id_path = $node_id_path;
        $this->accessor = PropertyAccess::createPropertyAccessor();
        $this->leaf_selections_only = false;
    }

    /**
     * @return HierarchyFormatterInterface
     */
    public function getFormatter()
    {
        return $this->formatter;
    }

    /**
     * @return HierarchyNode[]
     */
    public function getRootNodes()
    {
        return $this->root_nodes;
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
        $choices = array();
        $labels = array();

        if (!$this->leaf_selections_only) {
            /** @var HierarchyNode $node */
            foreach ($this->getFlattened() as $node) {
                $label = (string)$node;
                $key = $this->getNodeId($node);

                $choices[$key] = $key;
                $labels[$key] = $label;
            }

            return new HierarchyChoiceList($choices, $labels);
        }

        /** @var HierarchyNode $node */
        foreach ($this as $node) {
            if (count($node)) {
                $choices[(string)$node] = $node->getChoices();
                $labels[(string)$node] = $node->getLabels();
            } else {
                $choices[$this->getNodeId($node)] = $this->getNodeId($node);
                $labels[$this->getNodeId($node)] = (string)$node;
            }
        }

        return new HierarchyChoiceList($choices, $labels);
    }

    /**
     * @param HierarchyFormatterInterface $formatter
     */
    public function setFormatter(HierarchyFormatterInterface $formatter)
    {
        $this->formatter = $formatter;
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Retrieve an external iterator
     *
     * @link http://php.net/manual/en/iteratoraggregate.getiterator.php
     * @return Traversable An instance of an object implementing <b>Iterator</b> or
     *       <b>Traversable</b>
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->root_nodes);
    }

    /**
     * (PHP 5 &gt;= 5.1.0)<br/>
     * Count elements of an object
     *
     * @link http://php.net/manual/en/countable.count.php
     * @return int The custom count as an integer.
     *       </p>
     *       <p>
     *       The return value is cast to an integer.
     */
    public function count()
    {
        return count($this->root_nodes);
    }

    /**
     * @param $accessor
     * @param $node
     * @return mixed
     */
    public function getNodeId(HierarchyNode $node)
    {
        return $this->accessor->getValue($node, $this->node_id_path ?: 'data.id');
    }

    public function markOnlyLeafSelections()
    {
        $this->leaf_selections_only = true;
    }
}
 
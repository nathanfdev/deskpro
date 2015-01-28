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

namespace Application\AppBundle\Hierarchy;

use Symfony\Component\PropertyAccess\PropertyAccess;
use Traversable;

class Hierarchy implements \Countable, \IteratorAggregate
{
    /**
     * @var HierarchyFormatterInterface
     */
    protected $formatter;

    /**
     * @var HierarchyNode[]
     */
    protected $root_nodes;

    /**
     * @var string property path to unique ID of this node (ie. "data.id")
     */
    protected $node_id_path;

    /**
     * @var \Symfony\Component\PropertyAccess\PropertyAccessor
     */
    protected $accessor;

    /**
     * @param HierarchyNode[]             $root_nodes
     * @param HierarchyFormatterInterface $formatter
     * @param string|null $node_id_path
     */
    public function __construct(array $root_nodes, HierarchyFormatterInterface $formatter = null, $node_id_path = null)
    {
        $this->formatter = $formatter ? : new Formatter\FlatListFormatter();
        usort($root_nodes, function($node1, $node2) {
            return $node2->getOrder() - $node1->getOrder();
        });
        $this->root_nodes = $root_nodes;
        foreach ($root_nodes as $root_node) {
            $root_node->setHierarchy($this);
        }
        $this->node_id_path = $node_id_path;
        $this->accessor = PropertyAccess::createPropertyAccessor();
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
     * @param HierarchyNode $node
     * @return mixed
     */
    public function getNodeId(HierarchyNode $node)
    {
        return $this->accessor->getValue($node, $this->node_id_path ? : 'data.id');
    }

    /**
     * Will find you a node in the tree for a given node ID (as defined by this hierarchy's node property acessor)
     *
     * @param $node_id
     * @param bool $recursive
     * @return HierarchyNode|null
     */
    public function findNodeById($node_id, $recursive = true)
    {
        foreach ($this->root_nodes as $node) {
            if ($node_id == $this->getNodeId($node)) {
                return $node;
            }
        }

        if ($recursive) {
            foreach ($this->root_nodes as $node) {
                if ($result = $node->findChildById($node_id)) {
                    return $result;
                }
            }

        }

        return null;
    }
}
 
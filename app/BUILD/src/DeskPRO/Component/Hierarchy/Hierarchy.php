<?php

namespace DeskPRO\Component\Hierarchy;

use Symfony\Component\PropertyAccess\Exception\UnexpectedTypeException;
use Symfony\Component\PropertyAccess\PropertyAccess;

/**
 * Class Hierarchy.
 */
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
     * @var [id => node]
     */
    protected $map = [];

    /**
     * @param HierarchyNode[]             $root_nodes
     * @param HierarchyFormatterInterface $formatter
     * @param string|null                 $node_id_path
     */
    public function __construct(array $root_nodes, HierarchyFormatterInterface $formatter = null, $node_id_path = null)
    {
        $this->formatter    = $formatter ?: new Formatter\FlatListFormatter();
        $this->root_nodes   = $root_nodes;
        $this->node_id_path = $node_id_path;
        $this->accessor     = PropertyAccess::createPropertyAccessor();

        foreach ($root_nodes as $node) {
            $this->addNode($node);
        }

        // suppress the bug in php in some versions for modifying an array in usort
        @uksort($root_nodes, function ($a, $b) {
            $order1 = $this->root_nodes[$a]->getOrder();
            $order2 = $this->root_nodes[$b]->getOrder();

            if ($order1 === $order2) {
                return ($a < $b) ? -1 : 1;
            }

            return ($order2 < $order1) ? -1 : 1;
        });

        $this->root_nodes = array_values($root_nodes);
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
     * {@inheritdoc}
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->root_nodes);
    }

    /**
     * {@inheritdoc}
     */
    public function count()
    {
        return count($this->root_nodes);
    }

    /**
     * @param HierarchyNode $node
     *
     * @return mixed
     */
    public function getNodeId(HierarchyNode $node)
    {
        return $this->accessor->getValue($node, $this->node_id_path ?: 'data.id');
    }

    /**
     * Will find you a node in the tree for a given node ID (as defined by this hierarchy's node property acessor).
     *
     * @param $node_id
     * @param bool $recursive
     *
     * @return HierarchyNode|null
     */
    public function findNodeById($node_id, $recursive = true)
    {
        return isset($this->map[$node_id]) ? $this->map[$node_id] : null;
    }

    /**
     * @param HierarchyNode $node
     *
     * @throws \Exception
     */
    public function addNode(HierarchyNode $node)
    {
        try {
            $id = $this->getNodeId($node);
        } catch (UnexpectedTypeException $e) {
            throw new \Exception('HierarchyNode data should have an identifier');
        }
        if (isset($this->map[$id])) {
            return;
        }

        $node->setHierarchy($this);
        $this->map[$id] = $node;
    }
}

<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\PortalBundle\Twig\NodeVisitor;

use DeskPRO\Bundle\PortalBundle\Twig\Node\GroupItemNode;
use DeskPRO\Bundle\PortalBundle\Twig\Node\GroupNode;
use Twig_Environment;
use Twig_NodeInterface;

/**
 * Registers direct GroupItemNode children to the parent GroupNode
 * so the GroupNode knows which items it is responsible for rendering.
 *
 * You could achieve the same sort of thing without this visitor,
 * but it's a bit cleaner and easier to understand with it.
 */
class GroupVisitor implements \Twig_NodeVisitorInterface
{
    /**
     * @var GroupNode[]
     */
    private $group_stack = [];

    /**
     * Called before child nodes are visited.
     *
     * @param Twig_NodeInterface $node The node to visit
     * @param Twig_Environment   $env  The Twig environment instance
     *
     * @return Twig_NodeInterface The modified node
     */
    public function enterNode(Twig_NodeInterface $node, Twig_Environment $env)
    {
        if ($node instanceof GroupNode) {
            $this->group_stack[] = $node;
        } elseif ($node instanceof GroupItemNode && $this->group_stack) {
            $group = $this->group_stack[count($this->group_stack) - 1];
            $group->addGroupItem($node);
        }

        return $node;
    }

    /**
     * Called after child nodes are visited.
     *
     * @param Twig_NodeInterface $node The node to visit
     * @param Twig_Environment   $env  The Twig environment instance
     *
     * @return Twig_NodeInterface|false The modified node or false if the node must be removed
     */
    public function leaveNode(Twig_NodeInterface $node, Twig_Environment $env)
    {
        if ($node instanceof GroupNode) {
            array_pop($this->group_stack);
        }

        return $node;
    }

    /**
     * Returns the priority for this visitor.
     *
     * Priority should be between -10 and 10 (0 is the default).
     *
     * @return int The priority level
     */
    public function getPriority()
    {
        return 0;
    }
}

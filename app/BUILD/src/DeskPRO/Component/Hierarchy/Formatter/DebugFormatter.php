<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Component\Hierarchy\Formatter;

use DeskPRO\Component\Hierarchy\HierarchyNode;

class DebugFormatter extends AbstractFormatter
{
    /**
     * {@inheritdoc}
     */
    public function format(HierarchyNode $node)
    {
        return sprintf('%s - %s - %s', $node->getDepth(), $node->getOrder(), $this->getDataValue($node));
    }
}

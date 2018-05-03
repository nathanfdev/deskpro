<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Component\Hierarchy\Formatter;

use DeskPRO\Component\Hierarchy\HierarchyNode;

class DashesFormatter extends AbstractFormatter
{
    /**
     * {@inheritdoc}
     */
    public function format(HierarchyNode $node)
    {
        $prefix = str_repeat('--', $node->getDepth());

        return strlen($prefix) > 0 ? $prefix.' '.$this->getDataValue($node) : $this->getDataValue($node);
    }
}

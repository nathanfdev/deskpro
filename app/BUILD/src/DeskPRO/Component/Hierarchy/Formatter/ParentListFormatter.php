<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Component\Hierarchy\Formatter;

use DeskPRO\Component\Hierarchy\HierarchyNode;

class ParentListFormatter extends AbstractFormatter
{
    /**
     * {@inheritdoc}
     */
    public function format(HierarchyNode $node)
    {
        $formatted = '';

        foreach ($node->getParents() as $parent) {
            $formatted .= sprintf('%s > ', $parent->getData()->title);
        }

        return $formatted.sprintf('%s', $node->getData()->title);
    }
}

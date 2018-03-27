<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Component\Hierarchy\Formatter;

use DeskPRO\Component\Hierarchy\HierarchyNode;

class ParentListLanguageAwareFormatter extends AbstractLanguageAwareFormatter
{
    /**
     * {@inheritdoc}
     */
    public function format(HierarchyNode $node)
    {
        $formatted = '';

        foreach ($node->getParents() as $parent) {
            $formatted .= sprintf('%s > ', $this->getLanguageObjectPhrase($parent->getData()));
        }

        return $formatted.sprintf('%s', $this->getLanguageObjectPhrase($node->getData()));
    }
}

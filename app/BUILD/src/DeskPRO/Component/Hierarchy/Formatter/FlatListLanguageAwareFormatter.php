<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Component\Hierarchy\Formatter;

use DeskPRO\Component\Hierarchy\HierarchyNode;

class FlatListLanguageAwareFormatter extends AbstractLanguageAwareFormatter
{
    /**
     * {@inheritdoc}
     */
    public function format(HierarchyNode $node)
    {
        return sprintf('%s', $this->getLanguageObjectPhrase($node->getData()));
    }
}

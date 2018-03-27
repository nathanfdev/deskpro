<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Component\Hierarchy;

interface HierarchyFormatterInterface
{
    /**
     * Must turn the node into a string.
     *
     * @param HierarchyNode $node
     *
     * @return string
     */
    public function format(HierarchyNode $node);
}

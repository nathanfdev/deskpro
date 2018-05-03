<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Component\Hierarchy\Formatter;

use DeskPRO\Component\Hierarchy\HierarchyFormatterInterface;
use DeskPRO\Component\Hierarchy\HierarchyNode;
use Symfony\Component\PropertyAccess\PropertyAccess;

/**
 * The AbstractFormatter lets you easily get the string value of a node (optionally, with a property accessor).
 */
abstract class AbstractFormatter implements HierarchyFormatterInterface
{
    /**
     * @var string|null
     */
    private $stringPropertyPath;

    public function __construct($stringPropertyPath = null)
    {
        $this->stringPropertyPath = $stringPropertyPath;
    }

    public function getDataValue(HierarchyNode $node)
    {
        if ($this->stringPropertyPath) {
            $accessor = PropertyAccess::createPropertyAccessor();

            return $accessor->getValue($node->getData(), $this->stringPropertyPath);
        }

        return (string) $node->getData();
    }
}

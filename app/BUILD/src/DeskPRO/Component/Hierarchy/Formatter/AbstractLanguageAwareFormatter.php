<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Component\Hierarchy\Formatter;

use DeskPRO\Bundle\AppBundle\Language\LanguageManager;
use DeskPRO\Component\Hierarchy\HierarchyFormatterInterface;

/**
 * The AbstractLanguageAwareFormatter helps get translated phrases for objects (like departments, custom fields, etc).
 */
abstract class AbstractLanguageAwareFormatter implements HierarchyFormatterInterface
{
    /**
     * @var LanguageManager
     */
    private $language_manager;
    /**
     * @var null
     */
    private $property;

    public function __construct(LanguageManager $language_manager, $property = null)
    {
        $this->language_manager = $language_manager;
        $this->property         = $property;
    }

    public function getLanguageObjectPhrase($object, $property = null)
    {
        if (!$property) {
            $property = $this->property;
        }

        return $this->language_manager->objectPhrase($object, $property);
    }
}

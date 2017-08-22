<?php

namespace DeskPRO\Bundle\AppBundle\Form\CustomFieldManager;

class LegacyFieldNameResolvingStrategy implements FieldNameResolvingStrategy
{
    /**
     * @param string|$fieldName
     * @return string|null
     */
    public function resolve($fieldName)
    {
        $pattern = '#^field(\d+)$#';
        if (1 === preg_match($pattern, $fieldName, $matches)) {
            return $matches[1];
        }

        return null;
    }
}

<?php

namespace DeskPRO\Bundle\AppBundle\Form\CustomFieldManager;

class FieldNameResolverException extends \RuntimeException
{
    static public function missingStrategy($strategy, \Exception $previous = null)
    {
        $message = 'Resolving strategy: %s is either missing or could not be instantiated';
        $message = sprintf($message, $strategy);
        if (empty($previous)) {
            return new FieldNameResolverException($message);
        }

        return new FieldNameResolverException($message, 0, $previous);
    }

    /**
     * @param string $fieldName
     * @param string[] $resolvedNames
     * @return FieldNameResolverException
     */
    static public function ambiguousNameResolution($fieldName, array $resolvedNames)
    {
        $message = 'Field name: %s was resolved to more than one name: %s';
        $message = sprintf($message, $fieldName, implode(',', $resolvedNames));
        return new FieldNameResolverException($message);
    }
}

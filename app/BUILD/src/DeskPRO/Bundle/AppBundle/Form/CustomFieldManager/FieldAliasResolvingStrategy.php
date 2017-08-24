<?php

namespace DeskPRO\Bundle\AppBundle\Form\CustomFieldManager;

use DeskPRO\Bundle\AppBundle\ObjectAlias;

class FieldAliasResolvingStrategy implements FieldNameResolvingStrategy
{
    /**
     * @var ObjectAlias\ObjectIdResolver
     */
    private $objectIdResolver;

    /**
     * @param ObjectAlias\ObjectIdResolver $finder
     */
    public function __construct(ObjectAlias\ObjectIdResolver $finder)
    {
        $this->objectIdResolver = $finder;
    }

    /**
     * @param string|$alias
     * @return string|null
     */
    public function resolve($alias)
    {
        $pattern = '#^(\d+)$#'; // alias is the field id
        if (1 === preg_match($pattern, $alias, $matches)) {
            $actualInteger = (int) $matches[1];
            if ($matches[1] === (string) $actualInteger) {
                return $matches[1];
            }
        }

        $pattern = '#^field(\d+)$#'; // alias is field<Id>
        if (1 === preg_match($pattern, $alias, $matches)) {
            $actualInteger = (int) $matches[1];
            if ($matches[1] === (string) $actualInteger) {
                return $matches[1];
            }
        }

        $name = ObjectAlias\Converters::toNameFromString($alias);
        if (empty($name) || ! ObjectAlias\Name::isValidIdentifier($name)) {
            return null;
        }

        return $this->objectIdResolver->resolveAlias($name);
    }
}

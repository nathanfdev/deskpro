<?php

namespace DeskPRO\Bundle\AppBundle\ObjectAlias;

class DefaultIdResolvingStrategy implements AliasResolvingStrategy
{
    /**
     * @var ObjectIdResolver
     */
    private $objectIdResolver;

    /**
     * @param ObjectIdResolver $finder
     */
    public function __construct(ObjectIdResolver $finder)
    {
        $this->objectIdResolver = $finder;
    }

    /**
     * @param string|$alias
     * @return string|null
     */
    public function resolve($alias)
    {
        $name = Converters::toNameFromString($alias);
        if (empty($name) || !QualifiedName::isValidIdentifier($name)) {
            return null;
        }

        return $this->objectIdResolver->resolveAlias($name);
    }
}

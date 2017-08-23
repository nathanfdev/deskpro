<?php

namespace DeskPRO\Bundle\AppBundle\Form\CustomFieldManager;

use DeskPRO\Bundle\AppBundle\ObjectAlias;

class FieldAliasResolvingStrategy implements FieldNameResolvingStrategy
{
    /**
     * @var ObjectAlias\ObjectIdResolver
     */
    private $idFinder;

    public function __construct(ObjectAlias\ObjectIdResolver $finder)
    {
        $this->idFinder = $finder;
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

        $qualified = ObjectAlias\Converters::toQualifiedListFromString($alias);
        $unQualified = end($qualified);

        $pattern = '#^[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*#';
        if (1 !== preg_match($pattern, $unQualified, $matches)) {
            return null;
        }

        if (1 === count($qualified)) {
            return $this->idFinder->resolveAlias($unQualified);
        }
        return $this->idFinder->resolveQualifiedAlias($qualified);
    }
}

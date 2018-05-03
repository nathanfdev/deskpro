<?php

namespace DeskPRO\Bundle\AppBundle\ObjectAlias;

class Converters
{
    /**
     * @param QualifiedName $name
     * @return string
     */
    public static function toStringFromName( QualifiedName $name)
    {
        return implode(QualifiedName::QUALIFIER_SEPARATOR, $name->toList());
    }

    /**
     * @param string $alias
     *
     * @return QualifiedName
     */
    public static function toNameFromString($alias)
    {
        $parts = Converters::toNamePartsFromString($alias);
        if (empty($parts)) {
            return null;
        }

        return new QualifiedName(array_pop($parts), $parts);
    }

    /**
     * @param $alias
     *
     * @return array
     */
    public static function toNamePartsFromString( $alias)
    {
        return explode(QualifiedName::QUALIFIER_SEPARATOR, $alias);
    }

    /**
     * Flattens the list of object aliases into a list of strings which includes all possible named derivations of an alias
     *
     * @param ObjectAliasInterface[] $mappings
     *
     * @return array|string[]
     */
    public static function toMergedList($mappings)
    {
        $nameList = [];

        foreach ($mappings as $alias) {
            $nameList = array_merge($nameList, Converters::toList($alias));
        }
        return $nameList;
    }

    /**
     * Returns a list of aliases ordered from most specific to least specific
     *
     * @param \DeskPRO\Bundle\AppBundle\ObjectAlias\ObjectAliasInterface $mapping
     *
     * @return array|string[]
     */
    public static function toList(ObjectAliasInterface $mapping)
    {
        $aliases = [ $mapping->getObjectId(), $mapping->getQualifiedName() ];
        return array_values(array_filter($aliases, 'is_string'));
    }
}

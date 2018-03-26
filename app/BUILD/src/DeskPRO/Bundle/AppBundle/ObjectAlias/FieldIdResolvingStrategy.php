<?php

namespace DeskPRO\Bundle\AppBundle\ObjectAlias;

class FieldIdResolvingStrategy implements AliasResolvingStrategy
{
    /**
     * @param string|$alias
     * @return string|null
     */
    public function resolve($alias)
    {
        $pattern = '#^(\d+)$#'; // alias is the field id
        if (1 === preg_match($pattern, $alias, $matches)) {
            $actualInteger = (int)$matches[1];
            if ($matches[1] === (string)$actualInteger) {
                return $matches[1];
            }
        }

        $pattern = '#^field(\d+)$#'; // alias is field<Id>
        if (1 === preg_match($pattern, $alias, $matches)) {
            $actualInteger = (int)$matches[1];
            if ($matches[1] === (string)$actualInteger) {
                return $matches[1];
            }
        }

        return null;
    }
}

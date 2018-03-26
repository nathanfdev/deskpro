<?php

namespace DeskPRO\Bundle\AppBundle\ObjectAlias;

use DeskPRO\Bundle\AppBundle\ObjectAlias;

class Comparators
{
    /**
     * @param \DeskPRO\Bundle\AppBundle\ObjectAlias\ObjectAliasInterface $a
     * @param \DeskPRO\Bundle\AppBundle\ObjectAlias\ObjectAliasInterface $b
     *
     * @return bool
     */
    public static function equal(ObjectAlias\ObjectAliasInterface $a, ObjectAlias\ObjectAliasInterface $b)
    {
        if ($a->getObjectId() !== $b->getObjectId()) {
            return false;
        }

        if ($a->getObjectType() !== $b->getObjectType()) {
            return false;
        }

        if ($a->getQualifiedName() !== $b->getQualifiedName()) {
            return false;
        }

        return true;
    }
}

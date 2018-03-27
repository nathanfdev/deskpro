<?php

namespace DeskPRO\Bundle\AppBundle\Form\CustomFieldManager;

use DeskPRO\Bundle\AppBundle\ObjectAlias;

class FieldAliasConverter
{
    /**
     * @param \DeskPRO\Bundle\AppBundle\ObjectAlias\ObjectAliasInterface $mapping
     * @return array|string[]
     */
    public static function toList(ObjectAlias\ObjectAliasInterface $mapping)
    {
        $list = ObjectAlias\Converters::toList($mapping);
        $list[] = 'field' . $mapping->getObjectId();

        return $list;
    }
}

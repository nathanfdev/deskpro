<?php

namespace DeskPRO\Bundle\AppBundle\Serializer\Handler\Entity;

use DeskPRO\Bundle\AppBundle\Entity\IconProperty;
use DeskPRO\Bundle\AppBundle\Serializer\Model\IconProperty as IconPropertyModel;
use DeskPRO\Bundle\AppBundle\Serializer\Sideload\SideloadSerializationContext;

class IconPropertyHandler extends AbstractEntityHandler
{
    public static function getClassNames()
    {
        return IconProperty::class;
    }

    public function createModel($entity, SideloadSerializationContext $context)
    {
        /** @var IconProperty $entity */
        $model = new IconPropertyModel($entity);

        return $model;
    }
}

<?php

namespace DeskPRO\Bundle\AppBundle\Serializer\Handler\Entity;

use DeskPRO\Bundle\AppBundle\Entity\SplashImageProperty;
use DeskPRO\Bundle\AppBundle\Serializer\Model\SplashImageProperty as SplashImagePropertyModel;
use DeskPRO\Bundle\AppBundle\Serializer\Sideload\SideloadSerializationContext;

class SplashImagePropertyHandler extends AbstractEntityHandler
{
    public static function getClassNames()
    {
        return SplashImageProperty::class;
    }

    public function createModel($entity, SideloadSerializationContext $context)
    {
        /* @var SplashImageProperty $entity */
        return new SplashImagePropertyModel($entity);
    }
}

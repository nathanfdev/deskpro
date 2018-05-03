<?php

namespace DeskPRO\Bundle\AppBundle\Serializer\Handler\Other;

use Application\DeskPRO\CustomFields\FieldDisplayArray as FieldDisplayArrayObject;
use DeskPRO\Bundle\AppBundle\Serializer\Handler\Entity\AbstractEntityHandler;
use DeskPRO\Bundle\AppBundle\Serializer\Model\FieldDisplayArray;
use DeskPRO\Bundle\AppBundle\Serializer\Sideload\SideloadSerializationContext;

class FieldDisplayArrayHandler extends AbstractEntityHandler
{
    public static function getClassNames()
    {
        return FieldDisplayArrayObject::class;
    }

    /**
     * {@inheritdoc}
     *
     * @param FieldDisplayArrayObject $entity
     */
    public function createModel($entity, SideloadSerializationContext $context)
    {
        return new FieldDisplayArray($entity);
    }
}

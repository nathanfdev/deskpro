<?php

namespace DeskPRO\Bundle\AppBundle\Serializer\Handler\Entity;

use DeskPRO\Bundle\AppBundle\Serializer\Sideload\SideloadSerializationContext;
use JMS\Serializer\GraphNavigator;
use JMS\Serializer\Handler\SubscribingHandlerInterface;
use JMS\Serializer\JsonSerializationVisitor;

/**
 * Class AbstractEntityHandler.
 */
abstract class AbstractEntityHandler implements SubscribingHandlerInterface, EntityHandlerInterface
{
    /**
     * {@inheritdoc}
     */
    public static function getSubscribingMethods()
    {
        $methods = [];
        $classes = (array) static::getClassNames();

        foreach ($classes as $class) {
            $methods[] = [
                'direction' => GraphNavigator::DIRECTION_SERIALIZATION,
                'format'    => 'json',
                'type'      => $class,
                'method'    => 'serialize',
            ];
        }

        return $methods;
    }

    /**
     * @param JsonSerializationVisitor     $visitor
     * @param object                       $entity
     * @param array                        $type
     * @param SideloadSerializationContext $context
     *
     * @return mixed
     */
    public function serialize(JsonSerializationVisitor $visitor, $entity, $type, SideloadSerializationContext $context)
    {
        $model      = $this->createModel($entity, $context);
        $serialized = $context->accept($model);

        return $serialized;
    }

    /**
     * Returns api wrapper for the entity.
     *
     * @param object                       $entity
     * @param SideloadSerializationContext $context
     *
     * @return mixed
     */
    abstract public function createModel($entity, SideloadSerializationContext $context);
}

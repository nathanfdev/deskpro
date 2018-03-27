<?php

namespace DeskPRO\Bundle\AppBundle\Serializer\Handler\Common;

use DeskPRO\Bundle\AppBundle\Serializer\Handler\SerializerTypes;
use DeskPRO\Bundle\AppBundle\Serializer\Sideload\SideloadSerializationContext;
use JMS\Serializer\GraphNavigator;
use JMS\Serializer\Handler\SubscribingHandlerInterface;
use JMS\Serializer\JsonSerializationVisitor;

/**
 * Class MapHandler.
 */
class MapHandler implements SubscribingHandlerInterface
{
    /**
     * {@inheritdoc}
     */
    public static function getSubscribingMethods()
    {
        return [
            [
                'direction' => GraphNavigator::DIRECTION_SERIALIZATION,
                'format'    => 'json',
                'type'      => SerializerTypes::TYPE_MAP,
                'method'    => 'serialize',
            ],
        ];
    }

    /**
     * @param JsonSerializationVisitor     $visitor
     * @param array|\Traversable           $collection
     * @param array                        $type
     * @param SideloadSerializationContext $context
     *
     * @return mixed
     */
    public function serialize(JsonSerializationVisitor $visitor, $collection, $type, SideloadSerializationContext $context)
    {
        $entity_type = isset($type['params'][0]) ? $type['params'][0] : null;
        $result      = new \ArrayObject();

        foreach ($collection as $k => $entity) {
            $result[$k ?: 0] = $context->accept($entity, $entity_type);
        }

        return $result ?: new \ArrayObject();
    }
}

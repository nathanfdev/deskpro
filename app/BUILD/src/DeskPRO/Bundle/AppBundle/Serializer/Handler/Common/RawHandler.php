<?php

namespace DeskPRO\Bundle\AppBundle\Serializer\Handler\Common;

use DeskPRO\Bundle\AppBundle\Serializer\Handler\SerializerTypes;
use JMS\Serializer\GraphNavigator;
use JMS\Serializer\Handler\SubscribingHandlerInterface;
use JMS\Serializer\JsonSerializationVisitor;

/**
 * Class RawHandler.
 */
class RawHandler implements SubscribingHandlerInterface
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
                'type'      => SerializerTypes::TYPE_RAW,
                'method'    => 'serialize',
            ],
        ];
    }

    /**
     * @param JsonSerializationVisitor $visitor
     * @param mixed                    $value
     *
     * @return mixed
     */
    public function serialize(JsonSerializationVisitor $visitor, $value)
    {
        return $value;
    }
}

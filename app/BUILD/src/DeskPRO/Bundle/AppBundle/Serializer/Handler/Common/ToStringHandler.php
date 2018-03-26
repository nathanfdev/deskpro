<?php

namespace DeskPRO\Bundle\AppBundle\Serializer\Handler\Common;

use Application\DeskPRO\Entity\Labels\Label;
use DeskPRO\Bundle\AppBundle\Serializer\Handler\SerializerTypes;
use JMS\Serializer\GraphNavigator;
use JMS\Serializer\Handler\SubscribingHandlerInterface;
use JMS\Serializer\JsonSerializationVisitor;

/**
 * Class ToStringHandler.
 */
class ToStringHandler implements SubscribingHandlerInterface
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
                'type'      => SerializerTypes::TYPE_TO_STRING,
                'method'    => 'serialize',
            ],
        ];
    }

    /**
     * @param JsonSerializationVisitor $visitor
     * @param Label                    $entity
     *
     * @return mixed
     */
    public function serialize(JsonSerializationVisitor $visitor, $entity)
    {
        switch (get_class($entity)) {
            case 'DateTimeZone':
                /* @var \DateTimeZone $entity */
                return $entity->getName();
            default:
                return (string) $entity;
        }
    }
}

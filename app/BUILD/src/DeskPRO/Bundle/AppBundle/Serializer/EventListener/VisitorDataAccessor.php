<?php

namespace DeskPRO\Bundle\AppBundle\Serializer\EventListener;

use JMS\Serializer\JsonSerializationVisitor;
use JMS\Serializer\VisitorInterface;

/**
 * Class VisitorAccessor.
 */
class VisitorDataAccessor
{
    /**
     * @param VisitorInterface $visitor
     *
     * @return mixed
     */
    public static function getData(VisitorInterface $visitor)
    {
        $property = self::getReflectionProperty();
        $data     = $property->getValue($visitor);
        $property->setAccessible(false);

        return $data;
    }

    /**
     * @param VisitorInterface $visitor
     * @param mixed            $data
     */
    public static function setData(VisitorInterface $visitor, $data)
    {
        $property = self::getReflectionProperty();
        $property->setValue($visitor, $data);
        $property->setAccessible(false);
    }

    /**
     * @return \ReflectionProperty
     */
    private static function getReflectionProperty()
    {
        $property = new \ReflectionProperty(JsonSerializationVisitor::class, 'data');
        $property->setAccessible(true);

        return $property;
    }
}

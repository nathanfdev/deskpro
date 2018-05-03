<?php

namespace DeskPRO\Bundle\AppBundle\Serializer\Handler\Common;

use DeskPRO\Bundle\AppBundle\Serializer\Deferred\CallbackDeferredProperty;
use DeskPRO\Bundle\AppBundle\Serializer\Deferred\WrappedDeferred;
use DeskPRO\Bundle\AppBundle\Serializer\Handler\SerializerTypes;
use DeskPRO\Bundle\AppBundle\Serializer\Sideload\SideloadSerializationContext;
use JMS\Serializer\GraphNavigator;
use JMS\Serializer\Handler\SubscribingHandlerInterface;
use JMS\Serializer\JsonSerializationVisitor;

/**
 * Class DeferredHandler.
 */
class DeferredHandler implements SubscribingHandlerInterface
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
                'type'      => \Closure::class,
                'method'    => 'serializeClosure',
            ],
            [
                'direction' => GraphNavigator::DIRECTION_SERIALIZATION,
                'format'    => 'json',
                'type'      => SerializerTypes::TYPE_DEFERRED,
                'method'    => 'serialize',
            ],
            [
                'direction' => GraphNavigator::DIRECTION_SERIALIZATION,
                'format'    => 'json',
                'type'      => CallbackDeferredProperty::class,
                'method'    => 'serialize',
            ],
        ];
    }

    /**
     * @param JsonSerializationVisitor     $visitor
     * @param CallbackDeferredProperty     $deferred
     * @param array                        $type
     * @param SideloadSerializationContext $context
     *
     * @return mixed
     */
    public function serialize(JsonSerializationVisitor $visitor, $deferred, $type, SideloadSerializationContext $context)
    {
        $type = $this->sliceType($type);

        return new WrappedDeferred($deferred, $type);
    }

    /**
     * @param JsonSerializationVisitor     $visitor
     * @param \Closure                     $closure
     * @param array                        $type
     * @param SideloadSerializationContext $context
     *
     * @return WrappedDeferred
     */
    public function serializeClosure(JsonSerializationVisitor $visitor, $closure, $type, SideloadSerializationContext $context)
    {
        return new WrappedDeferred(new CallbackDeferredProperty($closure, []), null);
    }

    /**
     * @param $type
     *
     * @return mixed
     */
    protected function sliceType($type)
    {
        return $type['params'][0];
    }
}

<?php

namespace DeskPRO\Bundle\AppBundle\Serializer\Handler\Common;

use Application\DeskPRO\Domain\DomainObject;
use DeskPRO\Bundle\AppBundle\Entity\EntityInterface;
use DeskPRO\Bundle\AppBundle\Serializer\Handler\SerializerTypes;
use DeskPRO\Bundle\AppBundle\Serializer\Sideload\InlineEntitySideload;
use DeskPRO\Bundle\AppBundle\Serializer\Sideload\SideloadSerializationContext;
use DeskPRO\Component\Util\TypeUtils;
use JMS\Serializer\GraphNavigator;
use JMS\Serializer\Handler\SubscribingHandlerInterface;
use JMS\Serializer\JsonSerializationVisitor;

/**
 * Class EntityHandler.
 */
class EntityHandler implements SubscribingHandlerInterface
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
                'type'      => SerializerTypes::TYPE_ENTITY,
                'method'    => 'serialize',
            ],
        ];
    }

    /**
     * @param JsonSerializationVisitor     $visitor
     * @param EntityInterface|DomainObject $entity
     * @param array                        $type
     * @param SideloadSerializationContext $context
     *
     * @return mixed
     */
    public function serialize(JsonSerializationVisitor $visitor, $entity, $type, SideloadSerializationContext $context)
    {
        // we have to check what we are adding in sideloads since we can't control what is exactly happening here
        // e.g. we cant control of person from organization should be sideloaded while from ticket should not.

        if (!$context->isDisabledSideloads()) {
            $snake = TypeUtils::getSnakeCaseBaseTypeName($entity);
            if ($entity->getId() && (
                    $context->getIncludesStrategy() === SideloadSerializationContext::INCLUDE_STRATEGY_DATA
                    || in_array($snake, $context->getIncludes())
                )
            ) {
                $context->getSideloadStore()->addSideload($entity);
                if ($context->isInlineSideloads()) {
                    return new InlineEntitySideload($snake, $entity->getId());
                }
            }
        }

        return $entity->getId();
    }
}

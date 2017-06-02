<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2017, DeskPRO Ltd.
 *
 * The license agreement under which this software is released
 * can be found at https://www.deskpro.com/eula/
 *
 * By using this software, you acknowledge having read the license
 * and agree to be bound thereby.
 *
 * Please note that DeskPRO is not free software. We release the full
 * source code for our software because we trust our users to pay us for
 * the huge investment in time and energy that has gone into both creating
 * this software and supporting our customers. By providing the source code
 * we preserve our customers' ability to modify, audit and learn from our
 * work. We have been developing DeskPRO since 2001, please help us make it
 * another decade.
 *
 * Like the work you see? Think you could make it better? We are always
 * looking for great developers to join us: http://www.deskpro.com/jobs/
 *
 * ~ Thanks, Everyone at Team DeskPRO
 */

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
            if ($context->getIncludesStrategy() === SideloadSerializationContext::INCLUDE_STRATEGY_DATA
                || in_array($snake, $context->getIncludes())) {
                $context->getSideloadStore()->addSideload($entity);

                if ($context->isInlineSideloads()) {
                    return new InlineEntitySideload($snake, $entity->getId());
                }
            }
        }

        return $entity->getId();
    }
}

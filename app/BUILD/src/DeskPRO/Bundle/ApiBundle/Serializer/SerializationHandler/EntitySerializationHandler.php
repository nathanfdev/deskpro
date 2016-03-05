<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2015, DeskPRO Ltd.
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

namespace DeskPRO\Bundle\ApiBundle\Serializer\SerializationHandler;

use Application\DeskPRO\Domain\DomainObject;
use DeskPRO\Bundle\AppBundle\Entity\EntityInterface;
use DeskPRO\Component\Util\TypeUtils;
use JMS\Serializer\GraphNavigator;
use JMS\Serializer\Handler\SubscribingHandlerInterface;
use JMS\Serializer\JsonSerializationVisitor;

class EntitySerializationHandler implements SubscribingHandlerInterface
{
    protected $sideloads;

    protected $classmap;

    public static function getSubscribingMethods()
    {
        return array(
            [
                'direction' => GraphNavigator::DIRECTION_SERIALIZATION,
                'format'    => 'json',
                'type'      => EntityInterface::SERIALIZER_TYPE,
                'method'    => 'serializeEntity',
            ],
        );
    }

    public function serializeEntity(
        JsonSerializationVisitor $visitor,
        $entity
    ) {
        $fqcn = get_class($entity);
        if (!isset($this->sideloads[$fqcn])) {
            $this->sideloads[$fqcn] = [];
        }
        $entity_id = $entity->getId();

        // todo move it to separate object
        $this->sideloads[$fqcn][]                                     = $entity_id;
        $this->classmap[TypeUtils::getSnakeCaseBaseTypeName($entity)] = $fqcn;

        /* @var EntityInterface|DomainObject $entity */
        return $entity_id;
    }
}

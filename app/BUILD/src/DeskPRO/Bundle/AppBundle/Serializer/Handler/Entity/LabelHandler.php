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

namespace DeskPRO\Bundle\AppBundle\Serializer\Handler\Entity;

use Application\DeskPRO\Entity\LabelDef;
use Application\DeskPRO\Entity\Labels\Label;
use DeskPRO\Bundle\AppBundle\Serializer\Deferred\CallbackDeferredProperty;
use DeskPRO\Bundle\AppBundle\Serializer\Handler\SerializerTypes;
use DeskPRO\Bundle\AppBundle\Serializer\Sideload\SideloadSerializationContext;
use DeskPRO\Component\Util\TypeUtils;
use Doctrine\ORM\EntityManager;
use JMS\Serializer\GraphNavigator;
use JMS\Serializer\Handler\SubscribingHandlerInterface;
use JMS\Serializer\JsonSerializationVisitor;

/**
 * Class LabelHandler.
 */
class LabelHandler implements SubscribingHandlerInterface
{
    /**
     * @var EntityManager
     */
    private $em;

    /**
     * @var array
     */
    private $labelsMap = [];

    /**
     * @var LabelDef[]
     */
    private $labelDefs = [];

    /**
     * Constructor.
     *
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribingMethods()
    {
        return [
            [
                'direction' => GraphNavigator::DIRECTION_SERIALIZATION,
                'format'    => 'json',
                'type'      => SerializerTypes::TYPE_LABEL,
                'method'    => 'serialize',
            ],
        ];
    }

    /**
     * @param JsonSerializationVisitor     $visitor
     * @param Label                        $entity
     * @param string                       $type
     * @param SideloadSerializationContext $context
     *
     * @return string
     */
    public function serialize(JsonSerializationVisitor $visitor, Label $entity, $type, SideloadSerializationContext $context)
    {
        $this->labelsMap[$entity->getType()][$entity->getLabel()] = true;

        $sideloads = $context->getSideloadStore();
        $sideloads->addCustomSideload(
            TypeUtils::getSnakeCaseBaseTypeName($entity),
            $entity->getLabel(),
            new CallbackDeferredProperty([$this, 'getLabelDefs'], [$entity])
        );

        return $entity->getLabel();
    }

    /**
     * @param Label $label
     *
     * @return null|LabelDef
     */
    public function getLabelDefs(Label $label)
    {
        if (!isset($this->labelDefs[$label->getType()])) {
            $qb = $this->em->createQueryBuilder();
            $qb
                ->select('d')
                ->from(LabelDef::class, 'd')
                ->where(
                    'd.label_type = :label_type',
                    'd.label IN (:labels)'
                )
                ->setParameter('label_type', $label->getType())
                ->setParameter('labels', array_keys($this->labelsMap[$label->getType()]))
            ;

            $this->labelDefs[$label->getType()] = [];

            /** @var LabelDef[] $result */
            $result = $qb->getQuery()->getResult();
            foreach ($result as $labelDef) {
                $this->labelDefs[$labelDef->getLabelType()][$labelDef->getLabel()] = $labelDef;
            }
        }

        $typeDefs = $this->labelDefs[$label->getType()];

        return isset($typeDefs[$label->getLabel()]) ? $typeDefs[$label->getLabel()] : null;
    }
}

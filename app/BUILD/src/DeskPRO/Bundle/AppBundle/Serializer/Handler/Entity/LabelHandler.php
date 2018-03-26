<?php

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

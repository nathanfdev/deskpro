<?php

namespace DeskPRO\Bundle\AppBundle\Serializer\Handler\Entity;

use Application\DeskPRO\Entity\CustomFieldData;
use DeskPRO\Bundle\AppBundle\Serializer\Handler\SerializerTypes;
use Doctrine\Common\Collections\Collection;
use JMS\Serializer\GraphNavigator;
use JMS\Serializer\Handler\SubscribingHandlerInterface;
use JMS\Serializer\JsonSerializationVisitor;

/**
 * Class CustomPerDataHandler.
 */
class CustomPerDataHandler implements SubscribingHandlerInterface
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
                'type'      => SerializerTypes::TYPE_CUSTOM_PER_DATA,
                'method'    => 'serialize',
            ],
        ];
    }

    /**
     * @param JsonSerializationVisitor $visitor
     * @param CustomFieldData[]        $collection
     *
     * @return mixed
     */
    public function serialize(JsonSerializationVisitor $visitor, $collection)
    {
        if (!$collection instanceof Collection) {
            throw new \RuntimeException('Expected instanceof '.Collection::class);
        }

        $result = [];
        foreach ($collection as $customData) {
            $customDef = $customData->getRootDefinition();
            $defId     = $customDef->getId();

            $choiceDef = $customData->getDefinition();
            $choiceId  = $choiceDef->getId();

            $result[$defId]['value'][]           = $choiceId;
            $result[$defId]['detail'][$choiceId] = [
                'id'    => $choiceDef->getId(),
                'title' => $choiceDef->getTitle(),
            ];
        }

        return $result ?: new \ArrayObject();
    }
}

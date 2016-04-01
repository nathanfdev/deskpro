<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2016, DeskPRO Ltd.
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

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\AppBundle\Serializer\Handler\Entity;

use Application\DeskPRO\Entity\CustomDataAbstract;
use Application\DeskPRO\Entity\CustomDefAbstract;
use DeskPRO\Bundle\AppBundle\Serializer\Handler\SerializerTypes;
use DeskPRO\Bundle\AppBundle\Serializer\Sideload\SideloadSerializationContext;
use Doctrine\Common\Collections\Collection;
use JMS\Serializer\GraphNavigator;
use JMS\Serializer\Handler\SubscribingHandlerInterface;
use JMS\Serializer\JsonSerializationVisitor;

/**
 * Class CustomDataHandler.
 */
class CustomDataHandler implements SubscribingHandlerInterface
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
                'type'      => SerializerTypes::TYPE_CUSTOM_DATA,
                'method'    => 'serialize',
            ],
        ];
    }

    /**
     * @param JsonSerializationVisitor     $visitor
     * @param CustomDataAbstract[]         $collection
     * @param array                        $type
     * @param SideloadSerializationContext $context
     *
     * @return mixed
     */
    public function serialize(JsonSerializationVisitor $visitor, $collection, $type, SideloadSerializationContext $context)
    {
        if (!$collection instanceof Collection) {
            throw new \RuntimeException('Expected instanceof '.Collection::class);
        }

        $result = [];

        foreach ($collection as $customData) {
            $customDef = $customData->field->getParent() ? $customData->field->getParent() : $customData->field;
            $defId     = $customDef->getId();

            switch ($customDef->getType()) {
                case CustomDefAbstract::TYPE_CHOICE:
                    $choiceDef = $customData->field;
                    $choiceId  = $choiceDef->getId();

                    $result[$defId]['value'][]           = $choiceId;
                    $result[$defId]['detail'][$choiceId] = [
                        'id'    => $choiceDef->getId(),
                        'title' => $choiceDef->getTitle(),
                    ];
                    break;

                case CustomDefAbstract::TYPE_DATE:
                case CustomDefAbstract::TYPE_DATETIME:
                    $value = $customData->getInput();

                    try {
                        $value = new \DateTime('@'.$value);
                    } catch (\Exception $e) {
                        try {
                            $value = new \DateTime($value);
                        } catch (\Exception $e) {
                            $value = null;
                        }
                    }

                    $result[$defId]['value'] = $context->accept($value);
                    break;

                default:
                    if ($customData->getData()) {
                        $value = $customData->getData();
                    } else {
                        $value = null;
                    }

                    $result[$defId]['value'] = $value;
                    break;
            }
        }

        return $result;
    }
}

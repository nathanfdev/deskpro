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

use Application\DeskPRO\Entity\CustomDataAbstract;
use Application\DeskPRO\Entity\CustomDefAbstract;
use DeskPRO\Bundle\AppBundle\Form\CustomFieldManager;
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
     * @param CustomDefAbstract $def
     * @return array|string[]
     */
    public static function serializeAliases(CustomDefAbstract $def)
    {
        $aliases = [];
        foreach ($def->getAliases() as $alias) {
            $aliases = array_merge($aliases,  CustomFieldManager\FieldAliasConverter::toList($alias));
        }
        sort($aliases);
        return array_unique($aliases);
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
                'type'      => SerializerTypes::TYPE_CUSTOM_DATA,
                'method'    => 'serialize',
            ],
        ];
    }

    /**
     * @param CustomDataAbstract $fieldData
     * @return CustomDefAbstract
     */
    public static function getFieldTypeDefinition(CustomDataAbstract $fieldData)
    {
        return  $fieldData->field->getParent() ? $fieldData->field->getParent() : $fieldData->field;
    }

    /**
     * @param CustomDefAbstract $def
     * @param CustomDataAbstract[] $choices
     * @param SideloadSerializationContext $context
     * @return array
     */
    public function serializeChoice(CustomDefAbstract $def, array $choices, SideloadSerializationContext $context)
    {
        $extractChoiceId = function (CustomDataAbstract $data) { return $data->field->getId(); };
        $mapToChoiceMap = function (array $map, CustomDataAbstract $data) {
            $choiceDef = $data->field;
            $map[$choiceDef->getId()] = [
                'id'    => $choiceDef->getId(),
                'title' => $choiceDef->getTitle()
            ];

            return $map;
        };

        $serialized = [
            'aliases' => CustomDataHandler::serializeAliases($def),
            'value' => array_map($extractChoiceId, $choices),
            'detail' => array_reduce($choices, $mapToChoiceMap, [])
        ];

        return $serialized;
    }

    /**
     * @param CustomDefAbstract $def
     * @param CustomDataAbstract $data
     * @param SideloadSerializationContext $context
     * @return array
     */
    public function serializeDateTime(CustomDefAbstract $def, CustomDataAbstract $data, SideloadSerializationContext $context)
    {
        $value = $data->getData();

        if ($value) {
            try {
                $value = new \DateTime('@'.$value);
            } catch (\Exception $e) {
                try {
                    $value = new \DateTime($value);
                } catch (\Exception $e) {
                    $value = null;
                }
            }
        } else {
            $value = null;
        }

        return [
            'aliases' => CustomDataHandler::serializeAliases($def),
            'value' => $context->accept($value)
        ];
    }

    /**
     * @param CustomDefAbstract $def
     * @param CustomDataAbstract $data
     * @param SideloadSerializationContext $context
     * @return array
     */
    public function serializeJson(CustomDefAbstract $def, CustomDataAbstract $data, SideloadSerializationContext $context)
    {
        $value = $data->getData();
        try {
            $value = json_decode($value);
            if (json_last_error() !== JSON_ERROR_NONE) {
                $value = null;
            }
        } catch (\Exception $e) {
            $value = null;
        }

        return [
            'aliases' => CustomDataHandler::serializeAliases($def),
            'value' => $value
        ];
    }

    /**
     * @param CustomDefAbstract $def
     * @param CustomDataAbstract[] $list
     * @param SideloadSerializationContext $context
     * @return array
     */
    public function serializeList(CustomDefAbstract $def, array $list, SideloadSerializationContext $context)
    {
        $extractValue = function (CustomDataAbstract $data) {
            return $data->getData();
        };

        $values = array_map($extractValue, $list);
        return [
            'aliases' => CustomDataHandler::serializeAliases($def),
            'value' => $values
        ];
    }

    /**
     * @param CustomDefAbstract $def
     * @param CustomDataAbstract $data
     * @param SideloadSerializationContext $context
     * @return array
     */
    public function serializeData(CustomDefAbstract $def, CustomDataAbstract $data, SideloadSerializationContext $context)
    {
        if ($data->getData()) {
            $value = $data->getData();
        } else {
            $value = null;
        }

        return [
            'aliases' => CustomDataHandler::serializeAliases($def),
            'value' => $value
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

        // split the result according to cardinality of value set
        $lists = []; // unlimited number of values
        $choices = []; // fixed number of values
        /** @var CustomDataAbstract[] $singles */
        $singles = []; // only one value
        foreach ($collection as $customData) {
            $customDef = CustomDataHandler::getFieldTypeDefinition($customData);
            $defId     = $customDef->getId();

            if ($customDef->isDataListType()) {
                $values = array_key_exists($defId, $lists) ? $lists[$defId] : [];
                array_push($values, $customData);
                $lists[$defId] = $values;
            } else if ($customDef->isChoiceType()) {
                $values = array_key_exists($defId, $choices) ? $choices[$defId] : [];
                array_push($values, $customData);
                $choices[$defId] = $values;
            }
            else {
                $singles[$defId] = $customData;
            }
        }

        /** @var CustomDataAbstract[] $customDataList */
        foreach ($lists as $customDataList) {
            $first = reset($customDataList);
            $def = CustomDataHandler::getFieldTypeDefinition($first);
            $serialized = $this->serializeList($def, $customDataList, $context);
            $result[$def->getId()] = $serialized;
        }

        /** @var CustomDataAbstract[] $customDataList */
        foreach ($choices as $customDataList) {
            $first = reset($customDataList);
            $def = CustomDataHandler::getFieldTypeDefinition($first);
            $serialized = $this->serializeChoice($def, $customDataList, $context);
            $result[$def->getId()] = $serialized;
        }

        foreach ($singles as $customData) {
            $def = CustomDataHandler::getFieldTypeDefinition($customData);
            switch ($def->getType()) {
                case CustomDefAbstract::TYPE_DATE:
                case CustomDefAbstract::TYPE_DATETIME:
                    $serialized = $this->serializeDateTime($def, $customData, $context);
                    break;
                case CustomDefAbstract::TYPE_DATA_JSON:
                    $serialized = $this->serializeJson($def, $customData, $context);
                    break;
                default:
                    $serialized = $this->serializeData($def, $customData, $context);
                    break;
            }
            $result[$def->getId()] = $serialized;
        }

        return $result ?: new \ArrayObject();
    }
}

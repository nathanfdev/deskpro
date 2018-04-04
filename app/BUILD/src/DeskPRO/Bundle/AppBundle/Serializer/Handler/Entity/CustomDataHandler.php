<?php

namespace DeskPRO\Bundle\AppBundle\Serializer\Handler\Entity;

use Application\DeskPRO\Entity\CustomDataAbstract;
use Application\DeskPRO\Entity\CustomDefAbstract;
use DeskPRO\Bundle\AppBundle\Entity\Currency;
use DeskPRO\Bundle\AppBundle\Form\CustomFieldManager;
use DeskPRO\Bundle\AppBundle\Serializer\Handler\SerializerTypes;
use DeskPRO\Bundle\AppBundle\Serializer\Sideload\SideloadSerializationContext;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManager;
use JMS\Serializer\GraphNavigator;
use JMS\Serializer\Handler\SubscribingHandlerInterface;
use JMS\Serializer\JsonSerializationVisitor;

/**
 * Class CustomDataHandler.
 */
class CustomDataHandler implements SubscribingHandlerInterface
{
    /**
     * @var EntityManager
     */
    private $em;

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
     * @throws \Exception
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
        $lists   = []; // unlimited number of values
        $choices = []; // fixed number of values
        /** @var CustomDataAbstract[] $singles */
        $singles = []; // only one value
        foreach ($collection as $customData) {
            $customDef = self::getFieldTypeDefinition($customData);
            $defId     = $customDef->getId();

            if ($customDef->isDataListType()) {
                $values = array_key_exists($defId, $lists) ? $lists[$defId] : [];
                array_push($values, $customData);
                $lists[$defId] = $values;
            } elseif ($customDef->isChoiceType()) {
                $values = array_key_exists($defId, $choices) ? $choices[$defId] : [];
                array_push($values, $customData);
                $choices[$defId] = $values;
            } else {
                $singles[$defId] = $customData;
            }
        }

        /** @var CustomDataAbstract[] $customDataList */
        foreach ($lists as $customDataList) {
            $first                 = reset($customDataList);
            $def                   = self::getFieldTypeDefinition($first);
            $serialized            = $this->serializeList($def, $customDataList, $context);
            $result[$def->getId()] = $serialized;
        }

        /** @var CustomDataAbstract[] $customDataList */
        foreach ($choices as $customDataList) {
            $first                 = reset($customDataList);
            $def                   = self::getFieldTypeDefinition($first);
            $serialized            = $this->serializeChoice($def, $customDataList, $context);
            $result[$def->getId()] = $serialized;
        }

        foreach ($singles as $customData) {
            $def = self::getFieldTypeDefinition($customData);
            switch ($def->getType()) {
                case CustomDefAbstract::TYPE_DATE:
                case CustomDefAbstract::TYPE_DATETIME:
                    $serialized = $this->serializeDateTime($def, $customData, $context);
                    break;
                case CustomDefAbstract::TYPE_DATA_JSON:
                    $serialized = $this->serializeJson($def, $customData);
                    break;
                case CustomDefAbstract::TYPE_CURRENCY:
                    $serialized = $this->serializeCurrency($def, $customData);
                    break;
                default:
                    $serialized = $this->serializeData($def, $customData);
                    break;
            }
            $result[$def->getId()] = $serialized;
        }

        return $result ?: new \ArrayObject();
    }

    /**
     * @param CustomDefAbstract $def
     *
     * @return array|string[]
     */
    private function serializeAliases(CustomDefAbstract $def)
    {
        $aliases = [];
        foreach ($def->getAliases() as $alias) {
            $aliases = array_merge($aliases,  CustomFieldManager\FieldAliasConverter::toList($alias));
        }
        sort($aliases);

        return array_unique($aliases);
    }

    /**
     * @param CustomDataAbstract $fieldData
     *
     * @return CustomDefAbstract
     */
    private function getFieldTypeDefinition(CustomDataAbstract $fieldData)
    {
        return  $fieldData->field->getParent() ? $fieldData->field->getParent() : $fieldData->field;
    }

    /**
     * @param CustomDefAbstract    $def
     * @param CustomDataAbstract[] $choices
     *
     * @return array
     */
    private function serializeChoice(CustomDefAbstract $def, array $choices)
    {
        $extractChoiceId = function (CustomDataAbstract $data) {
            return $data->field->getId();
        };
        $mapToChoiceMap = function (array $map, CustomDataAbstract $data) {
            $choiceDef                = $data->field;
            $map[$choiceDef->getId()] = [
                'id'    => $choiceDef->getId(),
                'title' => $choiceDef->getTitle(),
            ];

            return $map;
        };

        $serialized = [
            'aliases' => self::serializeAliases($def),
            'value'   => array_map($extractChoiceId, $choices),
            'detail'  => array_reduce($choices, $mapToChoiceMap, []),
        ];

        return $serialized;
    }

    /**
     * @param CustomDefAbstract            $def
     * @param CustomDataAbstract           $data
     * @param SideloadSerializationContext $context
     *
     * @return array
     */
    private function serializeDateTime(CustomDefAbstract $def, CustomDataAbstract $data, SideloadSerializationContext $context)
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
            'aliases' => self::serializeAliases($def),
            'value'   => $context->accept($value),
        ];
    }

    /**
     * @param CustomDefAbstract  $def
     * @param CustomDataAbstract $data
     *
     * @return array
     */
    private function serializeJson(CustomDefAbstract $def, CustomDataAbstract $data)
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
            'aliases' => self::serializeAliases($def),
            'value'   => $value,
        ];
    }

    /**
     * @param CustomDefAbstract    $def
     * @param CustomDataAbstract[] $list
     *
     * @return array
     */
    private function serializeList(CustomDefAbstract $def, array $list)
    {
        $extractValue = function (CustomDataAbstract $data) {
            return $data->getData();
        };

        $values = array_map($extractValue, $list);

        return [
            'aliases' => self::serializeAliases($def),
            'value'   => $values,
        ];
    }

    /**
     * @param CustomDefAbstract  $def
     * @param CustomDataAbstract $data
     *
     * @return array
     */
    private function serializeCurrency(CustomDefAbstract $def, CustomDataAbstract $data)
    {
        $value      = null;
        $currencyId = $def->getOption('currency_id');
        if ($currencyId) {
            $currency = $this->em->getRepository(Currency::class)->find($currencyId);
            if ($currency) {
                $value = $data->getData() / $currency->getDelimiter();
                $value = number_format($value, $currency->getDecimalPlaces(), '.', ',');
            }
        }

        return [
            'aliases' => self::serializeAliases($def),
            'value'   => $value,
        ];
    }

    /**
     * @param CustomDefAbstract  $def
     * @param CustomDataAbstract $data
     *
     * @return array
     */
    private function serializeData(CustomDefAbstract $def, CustomDataAbstract $data)
    {
        if ($data->getData()) {
            $value = $data->getData();
        } else {
            $value = null;
        }

        return [
            'aliases' => self::serializeAliases($def),
            'value'   => $value,
        ];
    }
}

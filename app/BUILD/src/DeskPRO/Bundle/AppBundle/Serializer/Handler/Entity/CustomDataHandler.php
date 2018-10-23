<?php

namespace DeskPRO\Bundle\AppBundle\Serializer\Handler\Entity;

use Application\DeskPRO\Entity\Blob;
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
     * @var array
     */
    private $aliases = [];

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

        foreach ($collection as $customData) {
            $customDef = $customData->getRootField();
            $defId     = $customDef->getId();

            $result[$defId]['aliases'] = $this->serializeAliases($customDef);

            switch ($customDef->getType()) {
                case CustomDefAbstract::TYPE_FILE:
                    $blob = $this->em->getRepository(Blob::class)->find($customData->getValue());
                    if ($blob) {
                        $result[$defId]['value'][]  = $blob->getId();
                        $result[$defId]['detail'][] = $context->accept($blob);
                    }

                    break;

                case CustomDefAbstract::TYPE_DATA_LIST:
                    $result[$defId]['value'][] = $customData->getData();
                    break;

                case CustomDefAbstract::TYPE_CHOICE:
                    $choiceDef = $customData->getField();
                    $choiceId  = $choiceDef->getId();

                    $result[$defId]['value'][]           = $choiceId;
                    $result[$defId]['detail'][$choiceId] = [
                        'id'    => $choiceDef->getId(),
                        'title' => $choiceDef->getTitle(),
                    ];
                    break;

                case CustomDefAbstract::TYPE_DATE:
                case CustomDefAbstract::TYPE_DATETIME:
                    $result[$defId]['value'] = $this->serializeDateTimeValue($customData->getData(), $context);
                    break;
                case CustomDefAbstract::TYPE_DATA_JSON:
                    $result[$defId]['value'] = $this->serializeDataJsonValue($customData->getData());
                    break;
                case CustomDefAbstract::TYPE_CURRENCY:
                    $result[$defId]['value'] = $this->serializeCurrencyValue($customDef, $customData->getData());
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

        return $result ?: new \ArrayObject();
    }

    /**
     * @param mixed                        $value
     * @param SideloadSerializationContext $context
     *
     * @return \DateTime|null
     */
    private function serializeDateTimeValue($value, SideloadSerializationContext $context)
    {
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

        $value = $context->accept($value);

        return $value;
    }

    /**
     * @param string $value
     *
     * @return mixed
     */
    private function serializeDataJsonValue($value)
    {
        try {
            $value = json_decode($value);
            if (json_last_error() !== JSON_ERROR_NONE) {
                $value = null;
            }
        } catch (\Exception $e) {
            $value = null;
        }

        return $value;
    }

    /**
     * @param CustomDefAbstract $def
     * @param int               $value
     *
     * @return string
     */
    private function serializeCurrencyValue(CustomDefAbstract $def, $value)
    {
        $currencyId = $def->getOption('currency_id');
        if ($currencyId) {
            $currency = $this->em->getRepository(Currency::class)->find($currencyId);
            if ($currency) {
                $value = $value / $currency->getDelimiter();
                $value = number_format($value, $currency->getDecimalPlaces(), '.', ',');
            } else {
                $value = null;
            }
        } else {
            $value = null;
        }

        return $value;
    }

    /**
     * @param CustomDefAbstract $def
     *
     * @return array|string[]
     */
    private function serializeAliases(CustomDefAbstract $def)
    {
        if (!isset($this->aliases[$def->getId()])) {
            $aliases = [];
            foreach ($def->getAliases() as $alias) {
                $aliases = array_merge($aliases,  CustomFieldManager\FieldAliasConverter::toList($alias));
            }

            sort($aliases);
            $this->aliases[$def->getId()] = array_unique($aliases);
        }

        return $this->aliases[$def->getId()];
    }
}

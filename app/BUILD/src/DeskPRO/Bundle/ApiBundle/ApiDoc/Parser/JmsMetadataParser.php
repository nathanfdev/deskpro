<?php

namespace DeskPRO\Bundle\ApiBundle\ApiDoc\Parser;

use DeskPRO\Bundle\AppBundle\Serializer\Handler\SerializerTypes;
use JMS\Serializer\Metadata\PropertyMetadata;

/**
 * Class JmsMetadataParser.
 */
class JmsMetadataParser extends \Nelmio\ApiDocBundle\Parser\JmsMetadataParser
{
    /**
     * It's used to handle our super-trouper custom type called "entity"
     * So if it's entity array - then in doc you'll see something like
     * "array of integer ids (EntityName)".
     * In case it's not an array, but entity - then you'll see text like below:
     * "integer id (EntityName)"
     * Everybody dance now!
     *
     * @param PropertyMetadata $item
     *
     * @return array
     */
    protected function processDataType(PropertyMetadata $item)
    {
        $item->type = $this->sliceDeferred($item->type); // we should just remove deferred wrapper for type

        if ($item->type['name'] === SerializerTypes::TYPE_COLLECTION || $item->type['name'] === SerializerTypes::TYPE_MAP) {
            $item->type['name'] = 'array';
        }

        foreach ($this->getSupportedTypes() as $supported_type) {
            if (
                ($nestedType = $this->checkCustom($item, NestedConfiguration::getConfig($supported_type)))
                && $nestedType instanceof NestedType
            ) {
                return $nestedType->composeResponse();
            }
        }

        return parent::processDataType($item);
    }

    /**
     * @param PropertyMetadata    $item
     * @param NestedConfiguration $config
     *
     * @return NestedType|bool
     */
    protected function checkCustom(PropertyMetadata $item, NestedConfiguration $config)
    {
        $type = $item->type;

        if ($this->checkArray($type)) {
            $type        = $this->sliceType($type);
            $nested_type = $config->getCollectionType();
        } else {
            $nested_type = $config->getType();
        }
        try {
            $inner_type = $this->sliceType($type);
            if ($type && $inner_type && $type['name'] === $config->getSerializerType()) {
                return new NestedType($inner_type['name'], $nested_type);
            }

            return false;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * @param array $type
     *
     * @return bool
     */
    protected function checkArray(array $type)
    {
        return isset($type['name']) && in_array($type['name'], ['array', 'ArrayCollection']);
    }

    /**
     * @param $type
     *
     * @return mixed
     */
    protected function sliceType($type)
    {
        return isset($type['params']) && isset($type['params'][0]) ? $type['params'][0] : false;
    }

    /**
     * @return array
     */
    protected function getSupportedTypes()
    {
        return [
            SerializerTypes::TYPE_ENTITY,
            SerializerTypes::TYPE_TO_STRING,
            SerializerTypes::TYPE_CUSTOM_DATA,
        ];
    }

    /**
     * @param $type
     *
     * @return mixed
     */
    protected function sliceDeferred($type)
    {
        if ($type['name'] === SerializerTypes::TYPE_DEFERRED) {
            $type = $this->sliceType($type);
        }

        return $type;
    }
}

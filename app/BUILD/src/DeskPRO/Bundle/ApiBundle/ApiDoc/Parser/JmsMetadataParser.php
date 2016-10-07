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

namespace DeskPRO\Bundle\ApiBundle\ApiDoc\Parser;

use DeskPRO\Bundle\AppBundle\Serializer\Handler\SerializerTypes;
use JMS\Serializer\Metadata\PropertyMetadata;

/**
 * Class JmsMetadataParser.
 */
class JmsMetadataParser extends \Nelmio\ApiDocBundle\Parser\JmsMetadataParser
{
    /**
     * It's use to handle our super-trouper custom type called "entity"
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

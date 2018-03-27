<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\Serializer;

use Orb\Serializer\SerializerRegistry as BaseSerializerRegistry;

class SerializerRegistry extends BaseSerializerRegistry
{
    /**
     * {@inheritdoc}
     */
    public function serialize($data, $view = 'default', $format = 'array')
    {
        if ('array' !== $format) {
            throw new \LogicException('deskpro serializer can only output array format, currently');
        }

        return parent::serialize($data, $view, $format);
    }

    /**
     * Takes an array of arbitrary data and iterates through the array, serializing each element and returning
     * the result array. The is only 1 level deep, and is meant to make working with an array of API data (lists, etc)
     * a bit easier.
     *
     * @param array $array an array of data to be serialized
     *
     * @return array an array of serialized data
     */
    public function serializeArray(array $array)
    {
        $result = [];

        foreach ($array as $data) {
            $result[] = $this->serialize($data);
        }

        return $result;
    }
}

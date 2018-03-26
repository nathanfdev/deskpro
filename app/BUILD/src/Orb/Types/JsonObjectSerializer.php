<?php

/**
 * Orb.
 *
 * @category Types
 */

namespace Orb\Types;

class JsonObjectSerializer
{
    /**
     * @param JsonObjectSerializable $object
     *
     * @return string
     */
    public static function serialize(JsonObjectSerializable $object)
    {
        $class_name = get_class($object);
        $obj_data   = $object->serializeJsonArray();
        $data       = [
            '@CLASS' => $class_name,
            '@DATA'  => $obj_data,
        ];

        return json_encode($data);
    }

    /**
     * @param string $json_object
     *
     * @throws \InvalidArgumentException
     *
     * @return mixed
     */
    public static function unserialize($json_object)
    {
        $data = json_decode($json_object, true);

        if (!isset($data['@CLASS']) || !isset($data['@DATA'])) {
            throw new \InvalidArgumentException('Not a valid JsonObjectSerializable serialized string');
        }

        $class_name = $data['@CLASS'];
        $object     = $class_name::unserializeJsonArray($data['@DATA']);

        return $object;
    }
}

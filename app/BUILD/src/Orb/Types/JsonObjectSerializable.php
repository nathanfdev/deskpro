<?php

/**
 * Orb.
 *
 * @category Types
 */

namespace Orb\Types;

interface JsonObjectSerializable
{
    /**
     * Encodes the object to a PHP array for use in our object serializer.
     * MUST return a plain PHP array with primitive values.
     *
     * @return array
     */
    public function serializeJsonArray();

    /**
     * Takes an array of data that was serialized with serializeJsonArray() and re-constructs the PHP object from it.
     *
     * @param array $data
     *
     * @return $this
     */
    public static function unserializeJsonArray(array $data);
}

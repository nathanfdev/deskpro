<?php

namespace DpBehat\Data\Factory;

/**
 * Class PersonFactories.
 */
class Helper
{
    /**
     * @param array  $data
     * @param string $prop
     * @param object $object
     * @param string $setter
     * @param mixed  $default
     */
    public static function pick(&$data, $prop, $object, $setter, $default = null)
    {
        if (array_key_exists($prop, $data)) {
            $object->$setter($data[$prop]);
            unset($data[$prop]);
        } else {
            $object->$setter($default);
        }
    }
}

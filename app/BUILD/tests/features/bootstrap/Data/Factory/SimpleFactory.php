<?php

namespace DpBehat\Data\Factory;

use DpBehat\Data\DataNormalizer;

/**
 * Class SimpleFactory.
 *
 * This factory is a general object factory which just instantiates an object and sets properties via setters or
 * reflection.
 */
class SimpleFactory
{
    /**
     * @param string $class
     * @param array  $data
     *
     * @return object
     */
    public static function create($class, array $data = [])
    {
        $object = new $class();
        self::provide($object, $data);

        return $object;
    }

    /**
     * @param object $object
     * @param array  $data
     *
     * @throws \Exception
     *
     * @return object
     */
    public static function provide($object, array $data = [])
    {
        $class = get_class($object);

        foreach ($data as $prop => $value) {
            if (empty($value) && $value !== 0) {
                continue;
            }

            // Try setter
            $setter = DataNormalizer::underscoreToSetter($prop);
            if (method_exists($object, $setter)) {
                call_user_func([$object, $setter], $value);
                continue;
            }

            // Try add* method
            $add = DataNormalizer::underscoreToAddMethod($prop);
            if (method_exists($object, $add)) {
                call_user_func([$object, $add], $value);
                continue;
            }

            // Try CamelCase property
            $camelCaseProp = DataNormalizer::underscoreToCamelCase($prop);
            if (property_exists($class, $camelCaseProp)) {
                DataNormalizer::setProperty($object, $camelCaseProp, $value);
                continue;
            }

            // Try underscore_property
            if (property_exists($class, $prop)) {
                DataNormalizer::setProperty($object, $prop, $value);
                continue;
            }

            // Throw if non of the above worked
            throw new \Exception(
                "Can't set '{$prop}' on {$class} via {$setter}(), {$add}(), {$camelCaseProp}, {$prop}");
        }

        return $object;
    }
}

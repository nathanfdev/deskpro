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

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

namespace DpBehat\Data;

use Application\DeskPRO\Domain\BasicDomainObject;
use Orb\Util\Strings;

/**
 * Class DataNormalizer.
 */
class DataNormalizer
{
    /**
     * @param string $prop
     *
     * @return string
     */
    public static function underscoreToCamelCase($prop)
    {
        return Strings::underscoreToCamelCase($prop);
    }

    /**
     * @param string $prop
     *
     * @return string
     */
    public static function underscoreToSetter($prop)
    {
        $prop = str_replace('_', ' ', $prop);
        $prop = ucwords($prop);
        $prop = str_replace(' ', '', $prop);
        $prop = "set{$prop}";

        return $prop;
    }

    /**
     * @param string $prop
     *
     * @return string
     */
    public static function underscoreToAddMethod($prop)
    {
        $prop = str_replace('_', ' ', $prop);
        $prop = ucwords($prop);
        $prop = str_replace(' ', '', $prop);
        $prop = "add{$prop}";

        return $prop;
    }

    /**
     * @param object $object
     * @param string $property
     * @param mixed  $value
     */
    public static function setProperty($object, $property, $value)
    {
        if ($object instanceof BasicDomainObject) {
            $object->{$property} = $value;
        } else {
            $reflectionObject   = new \ReflectionObject($object);
            $reflectionProperty = $reflectionObject->getProperty($property);
            $reflectionProperty->setAccessible(true);
            $reflectionProperty->setValue($object, $value);
        }
    }

    /**
     * Translate human readable array keys into underscored variable names.
     *
     * @param array $data
     *
     * @return array
     */
    public static function namedKeysToUnderscore(array $data)
    {
        $normalized = [];
        foreach ($data as $propName => $value) {
            $normalized[self::namedPropertyUnderscore($propName)] = $value;
        }

        return $normalized;
    }

    /**
     * @param string $prop
     *
     * @return string
     */
    public static function namedPropertyUnderscore($prop)
    {
        $prop = strtolower($prop);
        $prop = str_replace(' ', '_', $prop);

        return $prop;
    }

    /**
     * @param object $object
     * @param string $prop
     *
     * @throws \Exception
     *
     * @return mixed
     */
    public static function getNamedPropertyValue($object, $prop)
    {
        $underscore = self::namedPropertyUnderscore($prop);
        $camelCase  = self::underscoreToCamelCase($underscore);

        $reflectionObject = new \ReflectionObject($object);
        if ($reflectionObject->hasProperty($underscore)) {
            $reflectionProperty = $reflectionObject->getProperty($underscore);
        } elseif ($reflectionObject->hasProperty($camelCase)) {
            $reflectionProperty = $reflectionObject->getProperty($camelCase);
        } else {
            throw new \Exception("Neither $underscore nor $camelCase property exists on the passed object");
        }
        $reflectionProperty->setAccessible(true);

        return $reflectionProperty->getValue($object);
    }
}

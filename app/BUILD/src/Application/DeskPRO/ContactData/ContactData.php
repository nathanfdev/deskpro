<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\ContactData;

use Orb\Util\Strings;

class ContactData
{
    /** @var array */
    protected static $instances = [];

    public static function getHandler($typename)
    {
        if (isset(self::$instances[$typename])) {
            return self::$instances[$typename];
        }

        $classname = 'Application\\DeskPRO\\ContactData\\'.ucfirst(Strings::underscoreToCamelCase($typename));
        if (!class_exists($classname)) {
            throw new \InvalidArgumentException("`$typename` is not a valid type");
        }

        self::$instances[$typename] = new $classname();

        return self::$instances[$typename];
    }
}

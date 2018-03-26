<?php

/**
 * Orb.
 */

namespace Orb\Validator;

/**
 * A static method on a validator that instantiates a default copy of the validator,
 * and checks a single value for validity.
 */
interface StaticValidator
{
    /**
     * Checks a value for validity. It just returns a true/false. If you need error codes
     * or error info, you must instantiate the object normally.
     *
     * @param $value
     *
     * @return bool
     */
    public static function isValueValid($value);
}

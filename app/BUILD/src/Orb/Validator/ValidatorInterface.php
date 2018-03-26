<?php

/**
 * Orb.
 */

namespace Orb\Validator;

/**
 * Validates a value.
 */
interface ValidatorInterface
{
    /**
     * Check to see if a value is valid or not.
     *
     * @return bool
     */
    public function isValid($value);

    /**
     * Get an array of errors.
     *
     * @return array
     */
    public function getErrors();
}

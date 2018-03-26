<?php

/**
 * Orb.
 */

namespace Orb\Validator;

class NotEmpty extends AbstractValidator
{
    /**
     * Check $value to see if its valid.
     *
     * @return bool
     */
    protected function checkIsValid($value)
    {
        if (is_string($value)) {
            $value = trim($value);
        }

        if (empty($value)) {
            $this->addError('empty');

            return false;
        }

        return true;
    }
}

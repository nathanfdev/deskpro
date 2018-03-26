<?php

/**
 * Orb.
 */

namespace Orb\Validator;

class FalseyValue extends AbstractValidator
{
    /**
     * Check $value to see if its valid.
     *
     * @return bool
     */
    protected function checkIsValid($value)
    {
        if ($value === null || $value === '' || $value === '0' || $value === 0 || $value === 0.0) {
            return true;
        }

        if (is_array($value)) {
            if (empty($value)) {
                return true;
            }
        }

        $value = trim($value);

        if ($value === '' || $value === '0') {
            return true;
        }

        $this->addError('not_falsey');

        return false;
    }
}

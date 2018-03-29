<?php

/**
 * Orb.
 */

namespace Orb\Validator;

class TruthyValue extends FalseyValue
{
    /**
     * Check $value to see if its valid.
     *
     * @return bool
     */
    protected function checkIsValid($value)
    {
        $falsey = parent::checkIsValid($value);

        if ($falsey) {
            $this->addError('not_truthy');

            return false;
        }

        return true;
    }
}

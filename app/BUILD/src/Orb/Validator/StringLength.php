<?php

/**
 * Orb.
 */

namespace Orb\Validator;

use Orb\Util\Strings;

class StringLength extends AbstractValidator
{
    /** @var int */
    protected $min;
    /** @var int */
    protected $max;

    public function init()
    {
        $this->min = $this->getOption('min', -1);
        $this->max = $this->getOption('max', -1);
    }

    /**
     * Check $value to see if its valid.
     *
     * @return bool
     */
    protected function checkIsValid($value)
    {
        $value = trim($value);

        if ($this->min != -1) {
            if (Strings::utf8_strlen($value) < $this->min) {
                $this->addError('length_too_short');

                return false;
            }
        }

        if ($this->max != -1) {
            if (Strings::utf8_strlen($value) > $this->max) {
                $this->addError('length_too_long');

                return false;
            }
        }

        return true;
    }
}

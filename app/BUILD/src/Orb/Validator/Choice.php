<?php

/**
 * Orb.
 */

namespace Orb\Validator;

class Choice extends AbstractValidator
{
    /** @var array */
    protected $valid_choices;

    public function init()
    {
        $this->valid_choices = $this->getOption('valid_choices');
    }

    /**
     * Check $value to see if its valid.
     *
     * @return bool
     */
    protected function checkIsValid($value)
    {
        if (!in_array($value, $this->valid_choices)) {
            $this->addError('choice.invalid');

            return false;
        }

        return true;
    }
}

<?php

/**
 * Orb.
 */

namespace Orb\Validator;

class StringRegex extends AbstractValidator
{
    /** @var string */
    protected $regex;

    public function init()
    {
        $this->regex = $this->getOption('regex');
    }

    /**
     * Check $value to see if its valid.
     *
     * @return bool
     */
    protected function checkIsValid($value)
    {
        if (!preg_match($this->regex, $value)) {
            $this->addError('no_regex_match');

            return false;
        }

        return true;
    }
}

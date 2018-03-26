<?php

/**
 * Orb.
 */

namespace Orb\Validator;

class StringUrl extends AbstractValidator implements StaticValidator
{
    /** @var array */
    protected $_protocols = ['http', 'https'];

    /**
     * @param $value
     *
     * @return bool
     */
    public static function isValueValid($value)
    {
        $validator = new self();

        return $validator->isValid($value);
    }

    /**
     * Check $value to see if its valid.
     *
     * @return bool
     */
    protected function checkIsValid($value)
    {
        if (!preg_match('#^([a-zA-Z0-9_-]+)://#', $value, $match)) {
            $this->addError('no_protocol');

            return false;
        }

        if (!in_array(strtolower($match[1]), $this->_protocols)) {
            $this->addError('invalid_protocol');

            return false;
        }

        return true;
    }
}

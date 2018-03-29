<?php

/**
 * Orb.
 */

namespace Orb\Validator;

/**
 * A validator composite.
 */
class ValidatorChain extends AbstractValidator
{
    /**
     * Array of validators to run.
     *
     * @var array
     */
    protected $validators = [];

    /**
     * Add a validator to the chain.
     *
     * @param \Orb\Validator\AbstractValidator $validator        The validator to add
     * @param bool                             $break_on_invalid If the validator says the value is invalid, break the chain (stop executing further ones)
     */
    public function addValidator(\Orb\Validator\AbstractValidator $validator, $break_on_invalid = false)
    {
        $this->validators[] = [
            $validator,
            (bool) $break_on_invalid,
        ];
    }

    /**
     * Get an array of currently set validators.
     *
     * @return array
     */
    public function getValidators()
    {
        return $this->validators;
    }

    /**
     * Check $value to see if its valid.
     *
     * @return bool
     */
    protected function checkIsValid($value)
    {
        if (!$this->validators) {
            return true;
        }

        foreach ($this->validators as $x) {
            $validator        = $x[0];
            $break_on_invalid = $x[1];

            if (!$validator->isValid($value)) {
                $this->errors      = array_merge($this->errors, $validator->getErrors());
                $this->errors_info = array_merge($this->errors_info, $validator->getErrorsInfo());

                if ($break_on_invalid) {
                    break;
                }
            }
        }

        if (!$this->errors) {
            return true;
        }

        return false;
    }
}

<?php

namespace DeskPRO\Bundle\AppBundle\Validator\Constraints\CustomField;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class CurrencyValidator.
 */
class CurrencyValidator extends AbstractSingleValueValidator
{
    /**
     * {@inheritdoc}
     */
    protected function getValidators($data, AbstractCustomDefConstraint $constraint)
    {
        $validators = [];

        // Required validator
        if ($constraint->getCustomDefOption('required', true)) {
            $validators[] = new Assert\NotBlank();
        }

        return $validators;
    }
}

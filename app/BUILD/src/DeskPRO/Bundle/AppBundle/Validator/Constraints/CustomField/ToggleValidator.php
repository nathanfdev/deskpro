<?php

namespace DeskPRO\Bundle\AppBundle\Validator\Constraints\CustomField;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class ToggleValidator.
 */
class ToggleValidator extends AbstractSingleValueValidator
{
    /**
     * {@inheritdoc}
     */
    protected function getValidators($data, AbstractCustomDefConstraint $constraint)
    {
        $validators = [];

        // Required validator
        if ($constraint->getCustomDefOption('validation_type', true)) {
            $validators[] = new Assert\IsTrue();
        }

        return $validators;
    }
}

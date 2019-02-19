<?php

namespace DeskPRO\Bundle\AppBundle\Validator\Constraints\CustomField;

use DeskPRO\Bundle\AppBundle\Validator\Constraints as AppAssert;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class UrlValidator.
 */
class UrlValidator extends AbstractSingleValueValidator
{
    /**
     * {@inheritdoc}
     */
    protected function getValidators($data, AbstractCustomDefConstraint $constraint)
    {
        $validators = [];

        // Required validator
        if ($constraint->check_required && $constraint->getCustomDefOption('required', true)) {
            $validators[] = new Assert\NotBlank();
        }

        // Url validator
        $validators[] = new AppAssert\Url([
            'allowFile' => $constraint->custom_def->getOption('allow_file'),
        ]);

        return $validators;
    }
}

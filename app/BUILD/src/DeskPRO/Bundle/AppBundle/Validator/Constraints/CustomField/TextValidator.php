<?php

namespace DeskPRO\Bundle\AppBundle\Validator\Constraints\CustomField;

use Orb\Util\Strings;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class TextValidator.
 */
class TextValidator extends AbstractSingleValueValidator
{
    /**
     * {@inheritdoc}
     */
    protected function getValidators($data, AbstractCustomDefConstraint $constraint)
    {
        $validators = [];

        // Required validator
        if ($constraint->check_required && (
            $constraint->getCustomDefOption('required', true)
            || $constraint->getCustomDefOption('regex_required', true)
        )) {
            $validators[] = new Assert\NotBlank();
        }

        // Length validator
        $minLength = (int) $constraint->getCustomDefOption('min_length', true);
        $maxLength = (int) $constraint->getCustomDefOption('max_length', true);

        $lengthOptions = [];
        if ($minLength) {
            $lengthOptions['min'] = $minLength;
        }
        if ($maxLength) {
            $lengthOptions['max'] = $maxLength;
        }

        if (!empty($lengthOptions)) {
            $validators[] = new Assert\Length($lengthOptions);
        }

        // Regex
        $regexPattern = $constraint->getCustomDefOption('regex', true);
        if ($regexPattern) {
            $validators[] = new Assert\Regex([
                'pattern' => Strings::getInputRegexPattern($regexPattern),
            ]);
        }

        return $validators;
    }
}

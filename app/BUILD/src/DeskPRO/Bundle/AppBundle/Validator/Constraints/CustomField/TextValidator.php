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
        if ($constraint->getCustomDefOption('required', true) || $constraint->getCustomDefOption('regex_required', true)) {
            $validators[] = new Assert\NotBlank();
        }

        // Length validator
        $min_length = (int) $constraint->getCustomDefOption('min_length', true);
        $max_length = (int) $constraint->getCustomDefOption('max_length', true);

        $length_options = [];
        if ($min_length) {
            $length_options['min'] = $min_length;
        }
        if ($max_length) {
            $length_options['max'] = $max_length;
        }

        if (!empty($length_options)) {
            $validators[] = new Assert\Length($length_options);
        }

        // Regex
        $regex_pattern = $constraint->getCustomDefOption('regex', true);
        if ($regex_pattern) {
            $validators[] = new Assert\Regex([
                'pattern' => Strings::getInputRegexPattern($regex_pattern),
            ]);
        }

        return $validators;
    }
}

<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2017, DeskPRO Ltd.
 *
 * The license agreement under which this software is released
 * can be found at https://www.deskpro.com/eula/
 *
 * By using this software, you acknowledge having read the license
 * and agree to be bound thereby.
 *
 * Please note that DeskPRO is not free software. We release the full
 * source code for our software because we trust our users to pay us for
 * the huge investment in time and energy that has gone into both creating
 * this software and supporting our customers. By providing the source code
 * we preserve our customers' ability to modify, audit and learn from our
 * work. We have been developing DeskPRO since 2001, please help us make it
 * another decade.
 *
 * Like the work you see? Think you could make it better? We are always
 * looking for great developers to join us: http://www.deskpro.com/jobs/
 *
 * ~ Thanks, Everyone at Team DeskPRO
 */

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

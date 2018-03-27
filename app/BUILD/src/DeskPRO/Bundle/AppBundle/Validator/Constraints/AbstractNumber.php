<?php

namespace DeskPRO\Bundle\AppBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * Class AbstractCallNumber.
 */
abstract class AbstractNumber extends Constraint
{
    const MISSING_COUNTRY_CODE = 'missing_country_code';
    const INVALID_PHONE_NUMBER = 'invalid_phone_number_format';

    public $missingCountryCodeMessage = 'Country code is missing.';
    public $invalidFormatMessage      = 'Invalid phone number format.';
}

<?php

namespace DeskPRO\Bundle\AppBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * Class CountryCode.
 *
 * @Annotation
 * @Target({"PROPERTY", "METHOD", "ANNOTATION"})
 */
class CountryCode extends Constraint
{
    const INVALID_COUNTRY_CODE = 'invalid_country_code';

    public $message = 'Invalid country code';
}

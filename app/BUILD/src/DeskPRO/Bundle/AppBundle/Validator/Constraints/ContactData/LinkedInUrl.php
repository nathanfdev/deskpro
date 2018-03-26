<?php

namespace DeskPRO\Bundle\AppBundle\Validator\Constraints\ContactData;

use Symfony\Component\Validator\Constraint;

/**
 * Class LinkedInUrl.
 *
 * @Annotation
 * @Target({"PROPERTY", "METHOD", "ANNOTATION"})
 */
class LinkedInUrl extends Constraint
{
    const NOT_PROFILE_URL = 'linked_in_url';

    public $message = 'This value is not a valid profile URL.';
}

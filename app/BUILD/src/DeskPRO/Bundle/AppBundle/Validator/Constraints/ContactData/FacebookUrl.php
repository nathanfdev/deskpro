<?php

namespace DeskPRO\Bundle\AppBundle\Validator\Constraints\ContactData;

use Symfony\Component\Validator\Constraint;

/**
 * Class FacebookUrl.
 *
 * @Annotation
 * @Target({"PROPERTY", "METHOD", "ANNOTATION"})
 */
class FacebookUrl extends Constraint
{
    const NOT_PROFILE_URL = 'facebook_url';

    public $message = 'This value is not a valid profile URL.';
}

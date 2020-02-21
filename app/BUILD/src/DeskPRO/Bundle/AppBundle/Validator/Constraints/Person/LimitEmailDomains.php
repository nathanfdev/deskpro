<?php

namespace DeskPRO\Bundle\AppBundle\Validator\Constraints\Person;

use Symfony\Component\Validator\Constraint;

/**
 * Class LimitEmailDomains.
 *
 * @Annotation
 * @Target({"PROPERTY", "METHOD", "ANNOTATION"})
 */
class LimitEmailDomains extends Constraint
{
    const BAD_EMAIL_DOMAIN = 'bad_email_domain';

    public $message = 'Unable to use this email domain';
}

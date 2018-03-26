<?php

namespace DeskPRO\Bundle\AppBundle\Validator\Constraints\Person\Email;

use Symfony\Component\Validator\Constraint;

/**
 * Class ExistEmail.
 *
 * @Annotation
 * @Target({"PROPERTY", "METHOD", "ANNOTATION"})
 */
class ExistEmail extends Constraint
{
    const NON_EXISTING_EMAIL = 'email_not_found';

    /**
     * @var string
     */
    public $notFoundMessage = 'Email "{{ value }}" not found.';
}

<?php

namespace DeskPRO\Bundle\AppBundle\Validator\Constraints\Person\Email;

/**
 * Class FreeEmail.
 *
 * @Annotation
 * @Target({"CLASS", "PROPERTY", "METHOD", "ANNOTATION"})
 */
class FreeEmail extends AbstractEmail
{
    const DUPE_EMAIL = 'dupe_email';

    /**
     * {@inheritdoc}
     */
    public $message = 'Email "{{ email }}" is already in use by other user.';

    /**
     * {@inheritdoc}
     */
    public function validatedBy()
    {
        return 'free_email_validator';
    }

    /**
     * {@inheritdoc}
     */
    public function getErrorCode()
    {
        return self::DUPE_EMAIL;
    }
}

<?php

namespace DeskPRO\Bundle\AppBundle\Validator\Constraints\Person\Email;

/**
 * Class NotBannedEmail.
 *
 * @Annotation
 * @Target({"CLASS", "PROPERTY", "METHOD", "ANNOTATION"})
 */
class NotBannedEmail extends AbstractEmail
{
    const BANNED_EMAIL = 'banned_email';

    /**
     * {@inheritdoc}
     */
    public $message = 'Email "{{ email }}" is banned.';

    /**
     * {@inheritdoc}
     */
    public function validatedBy()
    {
        return 'not_banned_email_validator';
    }

    /**
     * {@inheritdoc}
     */
    public function getErrorCode()
    {
        return self::BANNED_EMAIL;
    }
}

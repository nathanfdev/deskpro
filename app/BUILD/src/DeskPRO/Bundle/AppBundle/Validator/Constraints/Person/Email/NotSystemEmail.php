<?php

namespace DeskPRO\Bundle\AppBundle\Validator\Constraints\Person\Email;

/**
 * Class NotSystemEmail.
 *
 * @Annotation
 * @Target({"CLASS", "PROPERTY", "METHOD", "ANNOTATION"})
 */
class NotSystemEmail extends AbstractEmail
{
    const SYSTEM_EMAIL = 'system_email';

    /**
     * {@inheritdoc}
     */
    public $message = 'Email "{{ email }}" is already being used as email account.';

    /**
     * {@inheritdoc}
     */
    public function validatedBy()
    {
        return 'not_system_email_validator';
    }

    /**
     * {@inheritdoc}
     */
    public function getErrorCode()
    {
        return self::SYSTEM_EMAIL;
    }
}

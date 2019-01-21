<?php

namespace DeskPRO\Bundle\AppBundle\Validator\Constraints\Person\Email;

use Symfony\Component\Validator\Constraint;

/**
 * Class ExistEmail.
 *
 * @Annotation
 * @Target({"PROPERTY", "METHOD", "ANNOTATION"})
 */
class NotAgentEmail extends Constraint
{
    const IS_AGENT_EMAIL = 'email_is_agent_email';

    /**
     * @var string
     */
    public $property;

    /**
     * @var string
     */
    public $isAgentMessage = 'Email "{{ email }}" is already being used as agent email.';
}

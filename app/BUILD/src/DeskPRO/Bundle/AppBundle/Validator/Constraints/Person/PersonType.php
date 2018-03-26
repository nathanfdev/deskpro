<?php

namespace DeskPRO\Bundle\AppBundle\Validator\Constraints\Person;

use Symfony\Component\Validator\Constraint;

/**
 * Class PersonRole.
 *
 * @Annotation
 * @Target({"PROPERTY", "METHOD", "ANNOTATION"})
 */
class PersonType extends Constraint
{
    const PERSON_NOT_USER  = 'person_not_user';
    const PERSON_NOT_AGENT = 'person_not_agent';

    /**
     * Could be agent or user.
     *
     * @var string
     */
    public $type;

    /**
     * @var string
     */
    public $notUserMessage = 'Person with identifier "{{ value }}" is not a user.';

    /**
     * @var string
     */
    public $notAgentMessage = 'Person with identifier "{{ value }}" is not an agent.';
}

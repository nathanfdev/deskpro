<?php

namespace DeskPRO\Bundle\AppBundle\Validator\Constraints\Person\Email;

use Symfony\Component\Validator\Constraint;

/**
 * Class AbstractEmail.
 */
abstract class AbstractEmail extends Constraint
{
    /**
     * @var string
     */
    public $property = 'email';

    /**
     * @var string
     */
    public $message = 'Email "{{ email }}" is not valid.';

    /**
     * {@inheritdoc}
     */
    public function getTargets()
    {
        return [self::CLASS_CONSTRAINT, self::PROPERTY_CONSTRAINT];
    }

    /**
     * @return string
     */
    abstract public function getErrorCode();
}

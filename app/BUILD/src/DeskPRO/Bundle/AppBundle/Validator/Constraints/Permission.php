<?php

namespace DeskPRO\Bundle\AppBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * Class Permission.
 */
class Permission extends Constraint
{
    const NO_PERMISSION = 'no_permission';

    /**
     * @var string
     */
    public $action;

    /**
     * @var string
     */
    public $message = 'You have no permission to modify "{{ value }}" object(s).';
}

<?php

namespace DeskPRO\Bundle\AppBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * Class Permission.
 */
class Permission extends Constraint
{
    const NO_PERMISSION        = 'no_permission';
    const NO_DELETE_PERMISSION = 'no_delete_permission';

    /**
     * @var string
     */
    public $action;

    /**
     * @var string
     */
    public $modifyMessage = 'You have no permission to modify "{{ value }}" object(s).';

    /**
     * @var string
     */
    public $deleteMessage = 'You have no permission to delete "{{ value }}" object(s).';
}

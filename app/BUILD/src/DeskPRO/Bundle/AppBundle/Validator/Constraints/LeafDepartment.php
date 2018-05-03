<?php

namespace DeskPRO\Bundle\AppBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * Class LeafDepartment.
 *
 * @Annotation
 * @Target({"PROPERTY", "METHOD", "ANNOTATION"})
 */
class LeafDepartment extends Constraint
{
    const NOT_ASSIGNABLE_DEPARTMENT = 'not_assignable_department';

    public $message = 'Unable to select parent department.';
}

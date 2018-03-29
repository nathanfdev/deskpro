<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\AppBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class ValidTermEngineTerm extends Constraint
{
    const ERROR_OP_NOT_SUPPORTED = 'op_not_supported';

    public $message = self::ERROR_OP_NOT_SUPPORTED;

    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}

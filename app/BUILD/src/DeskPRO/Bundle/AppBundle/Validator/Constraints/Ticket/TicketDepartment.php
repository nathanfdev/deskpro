<?php

namespace DeskPRO\Bundle\AppBundle\Validator\Constraints\Ticket;

use Symfony\Component\Validator\Constraint;

/**
 * Class TicketDepartment.
 *
 * @Annotation
 * @Target({"PROPERTY", "METHOD", "ANNOTATION", "CLASS"})
 */
class TicketDepartment extends Constraint
{
    const UNAUTHORIZED_DEPARTMENT = 'unauthorized_department';

    public $message = 'Insufficient permission.';

    /**
     * {@inheritdoc}
     */
    public function getTargets()
    {
        return [self::CLASS_CONSTRAINT, self::PROPERTY_CONSTRAINT];
    }
}

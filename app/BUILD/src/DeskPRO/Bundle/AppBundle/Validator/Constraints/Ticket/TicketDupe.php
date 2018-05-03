<?php

namespace DeskPRO\Bundle\AppBundle\Validator\Constraints\Ticket;

use Symfony\Component\Validator\Constraint;

/**
 * Class TicketDupe.
 *
 * @Annotation
 * @Target({"PROPERTY", "METHOD", "ANNOTATION", "CLASS"})
 */
class TicketDupe extends Constraint
{
    const DUPE_TICKET = 'dupe_ticket';

    public $message = 'Duplicate ticket.';

    /**
     * {@inheritdoc}
     */
    public function getTargets()
    {
        return [self::CLASS_CONSTRAINT, self::PROPERTY_CONSTRAINT];
    }
}

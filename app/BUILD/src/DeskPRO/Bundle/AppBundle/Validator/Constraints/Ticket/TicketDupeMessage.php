<?php

namespace DeskPRO\Bundle\AppBundle\Validator\Constraints\Ticket;

use Symfony\Component\Validator\Constraint;

/**
 * Class TicketDupe.
 *
 * @Annotation
 * @Target({"PROPERTY", "METHOD", "ANNOTATION", "CLASS"})
 */
class TicketDupeMessage extends Constraint
{
    const DUPE_TICKET_MESSAGE = 'dupe_ticket_message';

    public $message = 'Duplicate ticket message.';

    /**
     * {@inheritdoc}
     */
    public function getTargets()
    {
        return [self::CLASS_CONSTRAINT, self::PROPERTY_CONSTRAINT];
    }
}

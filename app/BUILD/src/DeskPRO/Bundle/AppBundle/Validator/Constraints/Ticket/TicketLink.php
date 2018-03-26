<?php

namespace DeskPRO\Bundle\AppBundle\Validator\Constraints\Ticket;

use Symfony\Component\Validator\Constraint;

/**
 * Class TicketLink.
 *
 * @Annotation
 */
class TicketLink extends Constraint
{
    const LINK_ITSELF = 'link_itself';

    public $message = 'Ticket is linked to itself.';

    /**
     * {@inheritdoc}
     */
    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}

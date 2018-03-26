<?php

namespace DeskPRO\Bundle\AppBundle\Validator\Constraints\Ticket;

use Symfony\Component\Validator\Constraint;

/**
 * Class TicketLayout.
 */
class TicketLayout extends Constraint
{
    /**
     * Could be "agent" or "user".
     *
     * @var string
     */
    public $context = 'agent';

    /**
     * {@inheritdoc}
     */
    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }

    /**
     * @return bool
     */
    public function isAgent()
    {
        return $this->context === 'agent';
    }
}

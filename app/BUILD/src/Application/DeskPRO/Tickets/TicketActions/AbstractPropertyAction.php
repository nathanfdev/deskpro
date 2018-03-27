<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\Tickets\TicketActions;

use Application\DeskPRO\Entity\Ticket;

/**
 * Basic action for properties.
 */
abstract class AbstractPropertyAction extends AbstractAction
{
    /**
     * @var mixed
     */
    protected $value;

    /**
     * Constructor.
     *
     * @param int $value
     */
    public function __construct($value = 0)
    {
        $this->value = $value;
    }

    /**
     * Get the property name on the ticket object.
     *
     * @return string
     */
    abstract public function getPropertyName();

    /**
     * Get the property value.
     *
     * @return mixed
     */
    public function getPropertyValue()
    {
        return $this->value;
    }

    /**
     * {@inheritdoc}
     */
    public function apply(Ticket $ticket)
    {
        $prop = $this->getPropertyName();
        $val  = $this->getPropertyValue();

        $ticket[$prop] = $val;
    }

    /**
     * {@inheritdoc}
     */
    public function merge(ActionInterface $otherAction)
    {
        return $otherAction;
    }
}

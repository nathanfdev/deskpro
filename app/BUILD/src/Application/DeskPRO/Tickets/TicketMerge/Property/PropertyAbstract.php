<?php

/**
 * DeskPRO.
 *
 * @category Tickets
 */

namespace Application\DeskPRO\Tickets\TicketMerge\Property;

use Application\DeskPRO\Entity\Ticket;

/**
 * A property is something that can be merged in a ticket.
 */
abstract class PropertyAbstract
{
    /**
     * @var \Application\DeskPRO\Entity\Ticket
     */
    protected $ticket;

    /**
     * @var \Application\DeskPRO\Entity\Ticket
     */
    protected $other_ticket;

    /**
     * @var string
     */
    protected $strategy = null;

    /**
     * @var array
     */
    protected $strategy_options = [];

    const STRATEGY_LEFT    = 'left';
    const STRATEGY_RIGHT   = 'right';
    const STRATEGY_COMBINE = 'merge';

    public function __construct(Ticket $ticket, Ticket $other_ticket)
    {
        $this->ticket       = $ticket;
        $this->other_ticket = $other_ticket;
    }

    /**
     * Merge the two tickets.
     */
    abstract public function merge();

    /**
     * Set the merge strategy (how to handle conflicts).
     *
     * @param string $strategy
     */
    public function setStrategy($strategy, array $options = [])
    {
        $this->strategy = $strategy;
        $this->options  = $options;
    }

    /**
     * @return string
     */
    public function getStrategy()
    {
        return $this->strategy;
    }

    /**
     * Get a strategy option.
     *
     * @param string $name    Name of the option
     * @param string $default The default value if it wasnt set
     *
     * @return mixed
     */
    public function getStrategyOption($name, $default = null)
    {
        return isset($this->strategy_options[$name]) ? $this->strategy_options[$name] : $default;
    }
}

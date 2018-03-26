<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\Tickets\TicketActions;

use Application\DeskPRO\Entity\Ticket;

/**
 * Important: Constructors shouldn't do any work or require any transient values like "current user."
 * The action should always be usable from any context if possible. But at the very least, getDescription()
 * needs to be able to run and return a suitable string.
 */
interface ActionInterface
{
    /**
     * Apply the action to the ticket.
     *
     * @param \Application\DeskPRO\Entity\Ticket $ticket
     */
    public function apply(Ticket $ticket);

    /**
     * Get an array of actions that would be performed on the ticket.
     *
     * @param Ticket $ticket
     *
     * @return array
     */
    public function getApplyActions(Ticket $ticket);

    /**
     * Merge this action into another, and return the new merged action.
     *
     * For example, if a property is set, then the "other" action would overwrite the
     * "this" action, so you could just return "other"
     *
     * But if you were adding a value to a collection, then you could merge the two collections
     * together so the new action had new items from both actions.
     *
     * @param ActionInterface $otherAction
     *
     * @return ActionInterface
     */
    public function merge(ActionInterface $otherAction);

    /**
     * Get a text description of the action.
     *
     * @param bool $as_html
     *
     * @return string
     */
    public function getDescription($as_html = true);

    /**
     * @param array $metadata
     *
     * @return mixed
     */
    public function setMetaData(array $metadata);

    /**
     * @param null $k
     * @param null $default
     *
     * @return array
     */
    public function getMetaData($k = null, $default = null);

    /**
     * @return bool
     */
    public function doPrepend();

    /**
     * @return string
     */
    public function getActionName();
}

<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\Tickets\Actions;

use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Tickets\ExecutorContextInterface;

/**
 * Interface MacroActionInterface.
 */
interface MacroActionInterface
{
    /**
     * Return an array of macros that the user does not have permission to use.
     * An empty array or null means there are no permission errors.
     *
     * @param Person                   $person
     * @param Ticket                   $ticket
     * @param ExecutorContextInterface $context
     *
     * @return array|null
     */
    public function getMacroPermissionErrors(Person $person, Ticket $ticket, ExecutorContextInterface $context);

    /**
     * @param Person                   $person
     * @param Ticket                   $ticket
     * @param ExecutorContextInterface $context
     */
    public function applyMacro(Person $person, Ticket $ticket, ExecutorContextInterface $context);
}

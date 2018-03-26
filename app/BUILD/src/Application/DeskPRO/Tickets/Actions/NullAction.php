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

class NullAction extends AbstractAction implements ActionInterface, MacroActionInterface
{
    public function applyAction(Ticket $ticket, ExecutorContextInterface $context)
    {
    }

    public function getMacroPermissionErrors(Person $person, Ticket $ticket, ExecutorContextInterface $context)
    {
        return [];
    }

    public function applyMacro(Person $person, Ticket $ticket, ExecutorContextInterface $context)
    {
    }
}

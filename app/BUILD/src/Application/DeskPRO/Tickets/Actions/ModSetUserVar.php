<?php

/**
 * DeskPRO.
 *
 * @category Tickets
 */

namespace Application\DeskPRO\Tickets\Actions;

use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Tickets\ExecutorContextInterface;

/**
 * Sets a user variable.
 *
 * @option string name
 * @option string value
 */
class ModSetUserVar extends AbstractAction implements ActionInterface
{
    /**
     * {@inheritdoc}
     */
    public function applyAction(Ticket $ticket, ExecutorContextInterface $context)
    {
        $name = $this->getActionOption('name');
        if (!$name) {
            return;
        }

        $value = $this->getActionOption('value', 'VALUE');
        $context->getUserVars()->set($name, $value);
    }
}

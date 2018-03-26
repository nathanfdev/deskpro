<?php

/**
 * DeskPRO.
 *
 * @category Tickets
 */

namespace Application\DeskPRO\Tickets\Actions;

use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Tickets\ExecutorContextInterface;
use Orb\Util\CheckedOptionsArray;

/**
 * Set the priority.
 *
 * @option int priority_id
 */
class SetPriority extends AbstractContainerAwareAction implements ActionInterface, MacroActionInterface, NoopableInterface
{
    /**
     * {@inheritdoc}
     */
    protected function getOptionsDef()
    {
        $options = new CheckedOptionsArray();
        $options->addRequiredNames('priority_id');

        return $options;
    }

    /**
     * {@inheritdoc}
     */
    public function applyAction(Ticket $ticket, ExecutorContextInterface $context)
    {
        $set_pri_id = $this->getActionOption('priority_id');

        if ($set_pri_id) {
            $pri = $this->getContainer()->getTicketPriorities()->getById($set_pri_id);
            if (!$pri) {
                return;
            }
        } else {
            $pri = null;
        }

        $ticket->priority = $pri;
    }

    /**
     * {@inheritdoc}
     */
    public function isNoop(Ticket $ticket, ExecutorContextInterface $context)
    {
        $set_pri_id    = $this->getActionOption('priority_id');
        $ticket_pri_id = $ticket->priority ? $ticket->priority->id : 0;

        if ($ticket_pri_id == $set_pri_id) {
            return true;
        }

        if ($set_pri_id) {
            $pri = $this->getContainer()->getTicketPriorities()->getById($set_pri_id);
            if (!$pri) {
                return true;
            }
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function getMacroPermissionErrors(Person $person, Ticket $ticket, ExecutorContextInterface $context)
    {
        if (!$person->PermissionsManager->TicketChecker->canModify($ticket, 'fields')) {
            return ['fields'];
        }

        return;
    }

    /**
     * {@inheritdoc}
     */
    public function applyMacro(Person $person, Ticket $ticket, ExecutorContextInterface $context)
    {
        $this->applyAction($ticket, $context);
    }
}

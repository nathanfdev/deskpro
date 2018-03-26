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
 * Set the workflow.
 *
 * @option int workflow_id
 */
class SetWorkflow extends AbstractContainerAwareAction implements ActionInterface, MacroActionInterface, NoopableInterface
{
    /**
     * {@inheritdoc}
     */
    protected function getOptionsDef()
    {
        $options = new CheckedOptionsArray();
        $options->addRequiredNames('workflow_id');

        return $options;
    }

    /**
     * {@inheritdoc}
     */
    public function applyAction(Ticket $ticket, ExecutorContextInterface $context)
    {
        $set_work_id = $this->getActionOption('workflow_id');

        if ($set_work_id) {
            $work = $this->getContainer()->getTicketWorkflows()->getById($set_work_id);
            if (!$work) {
                return;
            }
        } else {
            $work = null;
        }

        $ticket->workflow = $work;
    }

    /**
     * {@inheritdoc}
     */
    public function isNoop(Ticket $ticket, ExecutorContextInterface $context)
    {
        $set_work_id    = $this->getActionOption('workflow_id');
        $ticket_work_id = $ticket->workflow ? $ticket->workflow->id : 0;

        if ($ticket_work_id == $set_work_id) {
            return true;
        }

        if ($set_work_id) {
            $work = $this->getContainer()->getTicketWorkflows()->getById($set_work_id);
            if (!$work) {
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

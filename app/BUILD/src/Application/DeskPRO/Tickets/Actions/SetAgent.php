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
 * Set the assigned agent.
 *
 * @option int agent_id   The agent to set. -1 for current user, 0 for unassigned and >0 for specified agent
 */
class SetAgent extends AbstractContainerAwareAction implements ActionInterface, MacroActionInterface, NoopableInterface
{
    /**
     * {@inheritdoc}
     */
    protected function getOptionsDef()
    {
        $options = new CheckedOptionsArray();
        $options->addRequiredNames('agent_id');

        return $options;
    }

    /**
     * @param $set_agent_id
     * @param ExecutorContextInterface $context
     *
     * @throws \InvalidArgumentException
     *
     * @return Person|null
     */
    private function resolveAgent($set_agent_id, ExecutorContextInterface $context)
    {
        if ($set_agent_id == -1) {
            if (!$context->getPersonContext() || !$context->getPersonContext()->is_agent) {
                throw new \RuntimeException();
            }
            $agent = $context->getPersonContext();
        } elseif ($set_agent_id == 0) {
            $agent = null;
        } else {
            $agent = $this->getContainer()->getAgentData()->get($set_agent_id);
            if (!$agent) {
                throw new \InvalidArgumentException();
            }
        }

        return $agent;
    }

    /**
     * {@inheritdoc}
     */
    public function applyAction(Ticket $ticket, ExecutorContextInterface $context)
    {
        try {
            $agent = $this->resolveAgent($this->getActionOption('agent_id'), $context);
        } catch (\RuntimeException $e) {
            return;
        } catch (\InvalidArgumentException $e) {
            return;
        }

        $ticket->agent = $agent;
    }

    /**
     * {@inheritdoc}
     */
    public function isNoop(Ticket $ticket, ExecutorContextInterface $context)
    {
        try {
            $agent = $this->resolveAgent($this->getActionOption('agent_id'), $context);
        } catch (\RuntimeException $e) {
            return true;
        } catch (\InvalidArgumentException $e) {
            return true;
        }

        $set_agent_id    = $agent ? $agent->id : 0;
        $ticket_agent_id = $ticket->agent ? $ticket->agent->id : 0;

        if ($ticket_agent_id == $set_agent_id) {
            return true;
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function getMacroPermissionErrors(Person $person, Ticket $ticket, ExecutorContextInterface $context)
    {
        $set_agent_id = $this->getActionOption('agent_id');
        if (!$person->PermissionsManager->TicketChecker->canModify($ticket, 'assign_agent')) {
            if ($set_agent_id == $person->getId()) {
                if ($person->PermissionsManager->TicketChecker->canModify($ticket, 'assign_self')) {
                    return;
                }

                return ['assign_self'];
            }

            return ['assign_agent'];
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

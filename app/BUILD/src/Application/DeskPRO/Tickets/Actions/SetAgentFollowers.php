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
 * Adds and removes agent followers from the ticket.
 *
 * @option int[] add_agent_ids     Array of agent IDs to add
 * @option int[] remove_agent_ids  Array of agent IDs to remove
 */
class SetAgentFollowers extends AbstractContainerAwareAction implements ActionInterface, MacroActionInterface
{
    /**
     * {@inheritdoc}
     */
    protected function getOptionsDef()
    {
        $options = new CheckedOptionsArray();
        $options->addValidNames('add_agent_ids', 'remove_agent_ids');

        return $options;
    }

    protected function resolveAgent(ExecutorContextInterface $context, $id)
    {
        if (!$id) {
            return;
        }

        if (-1 == $id) {
            if (!$context->getPersonContext() || !$context->getPersonContext()->is_agent) {
                return;
            }
            $agent = $context->getPersonContext();
        } else {
            $agent = $this->getContainer()->getAgentData()->get($id);
        }

        return $agent;
    }

    /**
     * {@inheritdoc}
     */
    public function applyAction(Ticket $ticket, ExecutorContextInterface $context)
    {
        //--------------------
        // Add followers
        //--------------------

        if ($add_agent_ids = $this->getActionOption('add_agent_ids')) {
            if (!is_array($add_agent_ids)) {
                $add_agent_ids = [$add_agent_ids];
            }
            foreach ($add_agent_ids as $agent_id) {
                if (!$agent = $this->resolveAgent($context, $agent_id)) {
                    continue;
                }

                if (!$ticket->participants->contains($agent)) {
                    $ticket->addParticipantPerson($agent);
                }
            }
        }

        //--------------------
        // Remove followers
        //--------------------

        if ($remove_agent_ids = $this->getActionOption('remove_agent_ids')) {
            if (!is_array($remove_agent_ids)) {
                $remove_agent_ids = [$remove_agent_ids];
            }
            foreach ($remove_agent_ids as $agent_id) {
                if (!$agent = $this->resolveAgent($context, $agent_id)) {
                    continue;
                }

                $ticket->removeParticipantPerson($agent);
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getMacroPermissionErrors(Person $person, Ticket $ticket, ExecutorContextInterface $context)
    {
        if (!$person->PermissionsManager->TicketChecker->canModify($ticket, 'cc')) {
            return ['cc'];
        }

        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function applyMacro(Person $person, Ticket $ticket, ExecutorContextInterface $context)
    {
        $this->applyAction($ticket, $context);
    }
}

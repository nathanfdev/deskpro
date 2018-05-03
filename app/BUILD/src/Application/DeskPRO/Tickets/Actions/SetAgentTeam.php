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
 * Sets the assigned agent team.
 *
 * @option int agent_team_id
 */
class SetAgentTeam extends AbstractContainerAwareAction implements ActionInterface, MacroActionInterface, NoopableInterface
{
    /**
     * {@inheritdoc}
     */
    protected function getOptionsDef()
    {
        $options = new CheckedOptionsArray();
        $options->addRequiredNames('agent_team_id');

        return $options;
    }

    /**
     * @param $set_team_id
     * @param ExecutorContextInterface $context
     *
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     *
     * @return \Application\DeskPRO\Entity\AgentTeam|null
     */
    private function resolveTeam(Ticket $ticket, $set_team_id, ExecutorContextInterface $context)
    {
        if ($set_team_id == -1) {
            if (!$context->getPersonContext() || !$context->getPersonContext()->is_agent) {
                throw new \RuntimeException();
            }
            $agent = $context->getPersonContext();
            $agent->loadHelper('Agent');
            $team = $agent->getPrimaryTeam();
        } elseif ($set_team_id == -2) {
            if (!$ticket->agent) {
                throw new \RuntimeException();
            }
            $team = $ticket->agent->getPrimaryTeam();
        } elseif ($set_team_id == 0) {
            $team = null;
        } else {
            $team = $this->getContainer()->getAgentData()->getTeam($set_team_id);
            if (!$team) {
                throw new \InvalidArgumentException();
            }
        }

        return $team;
    }

    /**
     * {@inheritdoc}
     */
    public function applyAction(Ticket $ticket, ExecutorContextInterface $context)
    {
        try {
            $team = $this->resolveTeam($ticket, $this->getActionOption('agent_team_id'), $context);
        } catch (\RuntimeException $e) {
            return;
        } catch (\InvalidArgumentException $e) {
            return;
        }

        $ticket->agent_team = $team;
    }

    /**
     * {@inheritdoc}
     */
    public function isNoop(Ticket $ticket, ExecutorContextInterface $context)
    {
        try {
            $team = $this->resolveTeam($ticket, $this->getActionOption('agent_team_id'), $context);
        } catch (\RuntimeException $e) {
            return true;
        } catch (\InvalidArgumentException $e) {
            return true;
        }

        $set_team_id    = $team ? $team->getId() : 0;
        $ticket_team_id = $ticket->agent_team ? $ticket->agent_team->getId() : 0;

        if ($ticket_team_id == $set_team_id) {
            return true;
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function getMacroPermissionErrors(Person $person, Ticket $ticket, ExecutorContextInterface $context)
    {
        if (!$person->PermissionsManager->TicketChecker->canModify($ticket, 'assign_team')) {
            return ['assign_team'];
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

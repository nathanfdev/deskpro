<?php

/**
 * DeskPRO.
 *
 * @category Tickets
 */

namespace Application\DeskPRO\Tickets\Actions;

use Application\DeskPRO\DBAL\Connection;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Tickets\ExecutorContextInterface;
use Orb\Util\CheckedOptionsArray;

/**
 * Sets a flag for a ticket. In a trigger context, this sets on every agent account.
 * In a macro context, only sets the flag on the current agent.
 *
 * @option string color
 */
class SetFlag extends AbstractContainerAwareAction implements ActionInterface, MacroActionInterface
{
    /**
     * {@inheritdoc}
     */
    protected function getOptionsDef()
    {
        $options = new CheckedOptionsArray();
        $options->addRequiredNames('color');

        return $options;
    }

    /**
     * @param Connection $db
     * @param Ticket     $ticket
     * @param Person     $person
     * @param string     $color
     */
    private function saveFlag(Connection $db, $ticket, $person, $color)
    {
        $color ? $db->replace('tickets_flagged', [
                    'person_id' => $person->id,
                    'ticket_id' => $ticket->id,
                    'color'     => $color,
                ])
                : $db->delete('tickets_flagged', [
                    'person_id' => $person->id,
                    'ticket_id' => $ticket->id,
                ]);
    }

    /**
     * {@inheritdoc}
     */
    public function applyAction(Ticket $ticket, ExecutorContextInterface $context)
    {
        foreach ($this->getContainer()->getAgentData()->getAgents() as $agent) {
            if ($agent->PermissionsManager->TicketChecker->canView($ticket)) {
                $this->saveFlag(
                    $this->getContainer()->getDb(),
                    $ticket,
                    $agent,
                    $this->getActionOption('color')
                );
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getMacroPermissionErrors(Person $person, Ticket $ticket, ExecutorContextInterface $context)
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function applyMacro(Person $person, Ticket $ticket, ExecutorContextInterface $context)
    {
        $this->saveFlag(
            $this->getContainer()->getDb(),
            $ticket,
            $person,
            $this->getActionOption('color')
        );
    }
}

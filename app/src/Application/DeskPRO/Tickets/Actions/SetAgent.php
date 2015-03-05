<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/  |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2014, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at https://www.deskpro.com/eula/                            |
|                                                                          |
| By using this software, you acknowledge having read the license          |
| and agree to be bound thereby.                                           |
|                                                                          |
| Please note that DeskPRO is not free software. We release the full       |
| source code for our software because we trust our users to pay us for    |
| the huge investment in time and energy that has gone into both creating  |
| this software and supporting our customers. By providing the source code |
| we preserve our customers' ability to modify, audit and learn from our   |
| work. We have been developing DeskPRO since 2001, please help us make it |
| another decade.                                                          |
|                                                                          |
| Like the work you see? Think you could make it better? We are always     |
| looking for great developers to join us: http://www.deskpro.com/jobs/    |
|                                                                          |
| ~ Thanks, Everyone at Team DeskPRO                                       |
\**************************************************************************/

/**
 * DeskPRO
 *
 * @package DeskPRO
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
     * {@inheritDoc}
     */
    protected function getOptionsDef()
    {
        $options = new CheckedOptionsArray();
        $options->addRequiredNames('agent_id');

        return $options;
    }


    /**
     * @param $set_agent_id
     * @param  ExecutorContextInterface  $context
     * @return Person|null
     * @throws \InvalidArgumentException
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
     * {@inheritDoc}
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
     * {@inheritDoc}
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
     * {@inheritDoc}
     */
    public function getMacroPermissionErrors(Person $person, Ticket $ticket, ExecutorContextInterface $context)
    {
        $set_agent_id = $this->getActionOption('agent_id');
        if (!$person->PermissionsManager->TicketChecker->canModify($ticket, 'assign_agent')) {
            if ($set_agent_id == $person->getId()) {
                if ($person->PermissionsManager->TicketChecker->canModify($ticket, 'assign_self')) {
                    return null;
                }

                return array('assign_self');
            }

            return array('assign_agent');
        }

        return null;
    }

    /**
     * {@inheritDoc}
     */
    public function applyMacro(Person $person, Ticket $ticket, ExecutorContextInterface $context)
    {
        $this->applyAction($ticket, $context);
    }
}

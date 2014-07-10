<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. http://www.deskpro.com/   |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2012, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at http://www.deskpro.com/license                           |
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
 * Sets the assigned agent team
 *
 * @option int agent_team_id
 */
class SetAgentTeam extends AbstractContainerAwareAction implements ActionInterface, MacroActionInterface, NoopableInterface
{
	/**
	 * {@inheritDoc}
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
	 * @return \Application\DeskPRO\Entity\AgentTeam|null
	 * @throws \RuntimeException
	 * @throws \InvalidArgumentException
	 */
	private function resolveTeam($set_team_id, ExecutorContextInterface $context)
	{
		if ($set_team_id == -1) {
			if (!$context->getPersonContext() || !$context->getPersonContext()->is_agent) {
				throw new \RuntimeException();
			}
			$agent = $context->getPersonContext();
			$agent->loadHelper('Agent');
			$teams = array_values($agent->getHelper('Agent')->getTeams());

			if (!count($teams)) {
				return null;
			}

			$team = $teams[0];
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
	 * {@inheritDoc}
	 */
	public function applyAction(Ticket $ticket, ExecutorContextInterface $context)
	{
		try {
			$team = $this->resolveTeam($this->getActionOption('agent_team_id'), $context);
		} catch (\RuntimeException $e) {
			return;
		} catch (\InvalidArgumentException $e) {
			return;
		}

		$ticket->agent_team = $team;
	}


	/**
	 * {@inheritDoc}
	 */
	public function isNoop(Ticket $ticket, ExecutorContextInterface $context)
	{
		try {
			$team = $this->resolveTeam($this->getActionOption('agent_team_id'), $context);
		} catch (\RuntimeException $e) {
			return true;
		} catch (\InvalidArgumentException $e) {
			return true;
		}

		$set_team_id    = $team ? $team->id : 0;
		$ticket_team_id = $ticket->agent_team ? $ticket->agent_team->id : 0;

		if ($ticket_team_id == $set_team_id) {
			return true;
		}

		return false;
	}


	/**
	 * {@inheritDoc}
	 */
	public function getMacroPermissionErrors(Person $person, Ticket $ticket, ExecutorContextInterface $context)
	{
		if (!$person->PermissionsManager->TicketChecker->canModify($ticket, 'assign_team')) {
			return array('assign_team');
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
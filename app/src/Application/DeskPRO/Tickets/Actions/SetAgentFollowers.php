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
 * Adds and removes agent followers from the ticket.
 *
 * @option int[] add_agent_ids     Array of agent IDs to add
 * @option int[] remove_agent_ids  Array of agent IDs to remove
 */
class SetAgentFollowers extends AbstractContainerAwareAction implements ActionInterface, MacroActionInterface
{
	/**
	 * {@inheritDoc}
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
			return null;
		}

		if (-1 == $id) {
			if (!$context->getPersonContext() || !$context->getPersonContext()->is_agent) {
				return null;
			}
			$agent = $context->getPersonContext();
		} else {
			$agent = $this->getContainer()->getAgentData()->get($id);
		}

		return $agent;
	}

	/**
	 * {@inheritDoc}
	 */
	public function applyAction(Ticket $ticket, ExecutorContextInterface $context)
	{
		#--------------------
		# Add followers
		#--------------------

		if ($add_agent_ids = $this->getActionOption('add_agent_ids')) {
			foreach ($add_agent_ids as $agent_id) {
				if (!$agent = $this->resolveAgent($context, $agent_id)) {
					continue;
				}

				if (!$ticket->participants->contains($agent)) {
					$ticket->addParticipantPerson($agent);
				}
			}
		}

		#--------------------
		# Remove followers
		#--------------------

		if ($remove_agent_ids = $this->getActionOption('remove_agent_ids')) {
			foreach ($remove_agent_ids as $agent_id) {
				if (!$agent = $this->resolveAgent($context, $agent_id)) {
					continue;
				}

				$ticket->removeParticipantPerson($agent);
			}
		}
	}


	/**
	 * {@inheritDoc}
	 */
	public function getMacroPermissionErrors(Person $person, Ticket $ticket, ExecutorContextInterface $context)
	{
		if (!$person->PermissionsManager->TicketChecker->canModify($ticket, 'cc')) {
			return array('cc');
		}

		return array();
	}


	/**
	 * {@inheritDoc}
	 */
	public function applyMacro(Person $person, Ticket $ticket, ExecutorContextInterface $context)
	{
		$this->applyAction($ticket, $context);
	}
}
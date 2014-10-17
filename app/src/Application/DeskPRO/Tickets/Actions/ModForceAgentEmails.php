<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/  |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2014, DeskPRO Ltd.             |
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

use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Tickets\ExecutorContextInterface;
use Orb\Util\CheckedOptionsArray;

/**
 * Adds agents to the subscription list, effectively forcing their notification
 * preference to be on for the current trigger run.
 */
class ModForceAgentEmails extends AbstractContainerAwareAction implements ActionInterface
{
	/**
	 * {@inheritDoc}
	 */
	protected function getOptionsDef()
	{
		$options = new CheckedOptionsArray();
		$options->addRequiredNames('agent_ids');
		return $options;
	}


	/**
	 * {@inheritDoc}
	 */
	public function applyAction(Ticket $ticket, ExecutorContextInterface $context)
	{
		$force_list = $context->getVars()->get('agent_force_subscription_list', array());

		$agent_ids = $this->getActionOption('agent_ids', array());
		if (!is_array($agent_ids)) {
			$agent_ids = array($agent_ids);
		}

		$agent_data = $this->getContainer()->getAgentData();
		$person_context = $context->getPersonContext();

		foreach ($agent_ids as $aid) {
			$force_list = array_merge($force_list, $agent_data->selectAgents($aid, $person_context, $ticket));
		}

		$force_list = array_unique($force_list);

		$ids = array_map(function($a) { return $a->id; }, $force_list);
		$context->getLogger()->info("[ModForceAgents] Force list: " . implode(', ', $ids));

		$context->getVars()->set('agent_force_subscription_list', $force_list);
	}
}
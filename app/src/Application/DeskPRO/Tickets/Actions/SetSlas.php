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
 * Adds or removes SLAs on a ticket.
 *
 * @option int[] add_sla_ids     Array of SLA IDs to add
 * @option int[] remove_sla_ids  Array of SLA IDs to remove
 */
class SetSlas extends AbstractContainerAwareAction implements ActionInterface, MacroActionInterface
{
	/**
	 * {@inheritDoc}
	 */
	protected function getOptionsDef()
	{
		$options = new CheckedOptionsArray();
		$options->addValidNames('add_sla_ids', 'remove_sla_ids');
		return $options;
	}


	/**
	 * {@inheritDoc}
	 */
	public function applyAction(Ticket $ticket, ExecutorContextInterface $context)
	{
		$em = $this->getContainer()->getEm();
		$ticket_slas = $this->getContainer()->getSystemService('ticket_slas');

		#--------------------
		# Add SLAs
		#--------------------

		if ($add_sla_ids = $this->getActionOption('add_sla_ids')) {
			foreach ($add_sla_ids as $sla_id) {
				$sla = $ticket_slas->getById($sla_id);
				if (!$sla) {
					continue;
				}

				if (!$ticket->hasSla($sla)) {
					$ticket_sla = $ticket->addSla($sla);
					$em->persist($ticket_sla);
				}
			}
		}

		#--------------------
		# Remove SLAs
		#--------------------

		if ($remove_sla_ids = $this->getActionOption('remove_sla_ids')) {
			foreach ($remove_sla_ids as $sla_id) {
				$sla = $ticket_slas->getById($sla_id);
				if (!$sla) {
					continue;
				}

				if (!$ticket->hasSla($sla)) {
					$ticket_sla = $ticket->removeSla($sla);
					$em->remove($ticket_sla);
				}
			}
		}
	}


	/**
	 * {@inheritDoc}
	 */
	public function getMacroPermissionErrors(Person $person, Ticket $ticket, ExecutorContextInterface $context)
	{
		if (!$person->PermissionsManager->TicketChecker->canModify($ticket, 'slas')) {
			return array('slas');
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

// xx bytes to prevent 4096 filesize (php bug)
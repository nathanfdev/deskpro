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
use Application\DeskPRO\Entity\TicketSla;
use Application\DeskPRO\Tickets\ExecutorContextInterface;
use Orb\Util\CheckedOptionsArray;

/**
 * Completes or uncompletes an SLA.
 *
 * @option int[] sla_ids       SLAs to set complete on
 * @option bool  sla_status    'auto', 'nochange', 'ok', 'warning', 'fail'
 *                             auto: recalc now, nochange: do nothing, ok: set to ok, warning: set to warning, fail: set to failed
 */
class SetSlaComplete extends AbstractContainerAwareAction implements ActionInterface, MacroActionInterface
{
	/**
	 * {@inheritDoc}
	 */
	protected function getOptionsDef()
	{
		$options = new CheckedOptionsArray();
		$options->addRequiredNames('sla_status', 'sla_ids');
		return $options;
	}


	/**
	 * {@inheritDoc}
	 */
	public function applyAction(Ticket $ticket, ExecutorContextInterface $context)
	{
		$sla_ids = $this->getActionOption('sla_ids');
		$sla_ids = array_combine($sla_ids, $sla_ids);

		$set_status = $this->getActionOption('sla_status');

		foreach ($ticket->ticket_slas as $ticket_sla) {
			if (!isset($sla_ids[-1]) && !isset($sla_ids[$ticket_sla->sla->id])) {
				continue;
			}

			$calc = $ticket_sla->sla->getCalculator();

			switch ($set_status) {
				case 'auto':
					$ticket_sla->warn_date = $calc->calculateWarnDate($ticket);
					$ticket_sla->fail_date = $calc->calculateFailDate($ticket);
					if ($ticket_sla->sla_status == 'ok' || $ticket_sla->sla_status == 'warning') {
						if ($calc->isTicketSlaFailed($ticket, $ticket_sla)) {
							$ticket_sla->sla_status = TicketSla::STATUS_FAIL;
						}
					} else if ($ticket_sla->sla_status == 'ok') {
						if ($calc->isTicketSlaWarning($ticket, $ticket_sla)) {
							$ticket_sla->sla_status = TicketSla::STATUS_WARNING;
						}
					} else {
						$ticket_sla->sla_status = TicketSla::STATUS_OK;
					}
					break;
				case 'ok':
					$ticket_sla->sla_status = TicketSla::STATUS_OK;
					break;
				case 'warning':
					$ticket_sla->sla_status = TicketSla::STATUS_WARNING;
					break;
				case 'fail':
					$ticket_sla->sla_status = TicketSla::STATUS_FAIL;
					break;
			}

			$completed_date = $calc->calculateCompletedDate($ticket);
			$ticket_sla->setIsCompleted(true, $completed_date);
			$this->getContainer()->getEm()->persist($ticket_sla);
			$this->getContainer()->getEm()->flush($ticket_sla);
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
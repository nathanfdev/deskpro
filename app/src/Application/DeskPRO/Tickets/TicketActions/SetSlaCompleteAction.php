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
 * @subpackage Tickets
 */

namespace Application\DeskPRO\Tickets\TicketActions;

use Application\DeskPRO\Tickets\TicketActions\ActionInterface;
use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\App;

use Orb\Util\Arrays;

/**
 * Set SLA complete
 */
class SetSlaCompleteAction extends AbstractAction
{
	protected $sla_complete;
	protected $sla_id;

	public function __construct($sla_complete, $sla_id)
	{
		$this->sla_complete = $sla_complete;
		$this->sla_id = $sla_id;
	}


	/**
	 * Apply the property to the ticket
	 *
	 * @param \Application\DeskPRO\Entity\Ticket $ticket
	 */
	public function apply(Ticket $ticket)
	{
		if ($this->sla_id) {
			$sla = App::getEntityRepository('DeskPRO:Sla')->find($this->sla_id);
			if (!$sla) {
				return;
			}

			$ticket_sla = $ticket->hasSla($sla);
			if (!$ticket_sla) {
				return;
			}

			$ticket_slas = array($ticket_sla);
		} else {
			$ticket_slas = $ticket->ticket_slas;
		}

		foreach ($ticket_slas AS $ticket_sla) {
			$ticket_sla->setIsCompleted($this->sla_complete);
		}
	}


	/**
	 * Get an array of actions that would be performed on the ticket
	 *
	 * @param \Application\DeskPRO\Entity\Ticket $ticket
	 */
	public function getApplyActions(Ticket $ticket)
	{
		return array(
			array('action' => 'set_sla_complete', 'sla_complete' => $this->sla_complete, 'sla_id' => $this->sla_id)
		);
	}


	/**
	 * @return integer
	 */
	public function getSlaComplete()
	{
		return $this->sla_complete;
	}


	/**
	 * @return integer
	 */
	public function getSlaId()
	{
		return $this->sla_id;
	}


	/**
	 * @param \Application\DeskPRO\Tickets\TicketActions\ActionInterface $other_action
	 * @return \Application\DeskPRO\Tickets\TicketActions\ActionInterface
	 */
	public function merge(ActionInterface $other_action)
	{
		return $other_action;
	}


	/**
	 * @return string
	 */
	public function getDescription($as_html = true)
	{
		if ($this->sla_id) {
			$sla = App::getEntityRepository('DeskPRO:Sla')->find($this->sla_id);
		} else {
			$sla = null;
		}

		if ($this->sla_complete) {
			if ($this->sla_id) {
				return 'Set SLA requirements to complete for SLA ' . ($sla ? $sla->title : '[unknown]');
			} else {
				return 'Set SLA requirements to complete';
			}
		} else {
			if ($this->sla_id) {
				return 'Set SLA requirements to incomplete for SLA ' . ($sla ? $sla->title : '[unknown]');
			} else {
				return 'Set SLA requirements to incomplete';
			}
		}
	}
}

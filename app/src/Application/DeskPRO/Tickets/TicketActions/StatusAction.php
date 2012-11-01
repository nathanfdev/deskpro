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

use Application\DeskPRO\App;
use Application\DeskPRO\Tickets\TicketActions\ActionInterface;
use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Entity\Person;

/**
 * Sets status
 */
class StatusAction extends AbstractAction implements PermissionableAction
{
	protected $status;

	public function __construct($status)
	{
		$this->setStatus($status);
	}

	public function setStatus($status)
	{
		if (!in_array($status, array(
			'awaiting_agent', 'awaiting_user', 'resolved', 'closed',
			'hidden.spam', 'hidden.validating', 'hidden.deleted'
		))) {
			throw new \InvalidArgumentException("Invalid status `$status`");
		}
		$this->status = $status;
	}

	/**
	 * True to stop processing actions after this one
	 *
	 * @return bool
	 */
	public function checkPermission(Ticket $ticket, Person $person)
	{
		// No change, sure they can apply no change
		if ($ticket->getStatusCode() == $this->status) {
			return true;
		}

		if (($this->status == 'hidden.deleted' || $this->status == 'hidden.spam') && !$person->PermissionsManager->TicketChecker->canDelete($ticket)) {
			return false;
		}
		if ($this->status == 'awaiting_agent' && !$person->PermissionsManager->TicketChecker->canModify($ticket, 'set_awaiting_agent')) {
			return false;
		}
		if ($this->status == 'awaiting_user' && !$person->PermissionsManager->TicketChecker->canModify($ticket, 'set_awaiting_user')) {
			return false;
		}
		if ($this->status == 'resolved' && !$person->PermissionsManager->TicketChecker->canModify($ticket, 'set_awaiting_user')) {
			return false;
		}

		return true;
	}

	/**
	 * Apply the property to the ticket
	 *
	 * @param \Application\DeskPRO\Entity\Ticket $ticket
	 */
	public function apply(Ticket $ticket)
	{
		if (strpos($this->status, '.') !== false) {
			list ($status, $hidden_status) = explode('.', $this->status, 2);
		} else {
			$status = $this->status;
			$hidden_status = null;
		}

		if ($hidden_status) {
			$ticket->setHiddenStatus($hidden_status);
		} else {
			$ticket->setStatus($status);
		}
	}


	/**
	 * Get an array of actions that would be performed on the ticket
	 *
	 * @param \Application\DeskPRO\Entity\Ticket $ticket
	 */
	public function getApplyActions(Ticket $ticket)
	{
		if ($ticket->getStatusCode() == $this->status) {
			return array();
		}

		return array(
			array('action' => 'status', 'status' => $this->status)
		);
	}


	/**
	 * Get the full status (stauts.hidden_status)
	 *
	 * @return string
	 */
	public function getFullStatus()
	{
		return $this->status;
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
        $tr = App::getTranslator();
		return $tr->phrase('admin.tickets.set_status_to_x', array('status' => $tr->phrase('agent.tickets.status_' . str_replace('.', '_', $this->status))));
	}
}

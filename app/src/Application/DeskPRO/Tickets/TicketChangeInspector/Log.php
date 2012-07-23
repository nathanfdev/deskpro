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

namespace Application\DeskPRO\Tickets\TicketChangeInspector;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity;

use Application\DeskPRO\Tickets\TicketChangeTracker;
use Application\DeskPRO\Tickets\TicketChangeInspector\LogActions\LogActionInterface;

use Orb\Util\Strings;

class Log
{
	/**
	 * @var \TicketChangeTracker\DeskPRO\Tickets\TicketListener
	 */
	protected $tracker;

	/**
	 * @var \Application\DeskPRO\Entity\Ticket
	 */
	protected $ticket;

	/**
	 * @var \Application\DeskPRO\Entity\Person
	 */
	protected $person_context = -1;

	public function __construct(TicketChangeTracker $tracker)
	{
		$this->tracker = $tracker;
		$this->ticket = $tracker->getTicket();
	}

	public function runPre()
	{
		// If we've got a new message, then we need to fetch previous
		// urgency resets since the last reply time.
		// If any are marked as reset, we'll need to apply that urgency
		// mod now

		$old_reply_time = $this->tracker->getChangedProperty('date_last_agent_reply');

		// Checking on date_last_user_reply means we can easily check
		// that there was a reply made just now, AND it was an agent reply
		if (!$old_reply_time) {
			return;
		}

		$old_reply_time = $old_reply_time['old'];

		// There was no old times
		if (!$old_reply_time) {
			return;
		}

		$logs = App::getOrm()->createQuery("
			SELECT l
			FROM DeskPRO:TicketLog l
			WHERE l.ticket = ?1 AND l.action_type = ?2 AND l.date_created >= ?3
		")->setParameter(1, $this->ticket)
		  ->setParameter(2, 'changed_urgency')
		  ->setParameter(3, $old_reply_time->format('Y-m-d H:i:s'))
		  ->execute();

		$mod = 0;

		foreach ($logs as $log) {
			if (isset($log['details']['reset_next_reply'])) {
				$mod += $log['details']['reset_next_reply'];
			}
		}

		if ($mod) {
			$this->ticket->modifyUrgency($mod);
		}
	}

	public function getLogActions()
	{
		$actions = array();

		if ($this->tracker->getExtra('trigger')) {
			$actions[] = new LogActions\TicketTriggers($this->tracker->getExtra('trigger'));
		}

		foreach ($this->tracker->getAllChangedProperties() as $prop => $all_info) {

			if ($prop == 'messages') {
				// Messages is a multi item already,
				// dont need an array wrapper
			} else {
				$all_info = array($all_info);

				// All others are single changes,
				// we wrap in an array for the foreach to work below
			}

			foreach ($all_info as $info) {
				$action = null;

				$old_val = null;
				$new_val = null;

				if (isset($info['old'])) $old_val = $info['old'];
				if (isset($info['new'])) $new_val = $info['new'];

				switch ($prop) {
					case 'agent':
						if (!$this->tracker->isNewTicket()) {
							$action = new LogActions\Agent($old_val, $new_val);
						}
						break;

					case 'agent_team':
						if (!$this->tracker->isNewTicket()) {
							$action = new LogActions\AgentTeam($old_val, $new_val);
						}
						break;

					case 'category':
						if (!$this->tracker->isNewTicket()) {
							$action = new LogActions\Category($old_val, $new_val);
						}
						break;

					case 'department':
						if (!$this->tracker->isNewTicket()) {
							$action = new LogActions\Department($old_val, $new_val);
						}
						break;

					case 'messages':
						if ($new_val) {
							$action = new LogActions\Message($new_val);
						} else {
							$action = new LogActions\MessageRemoved($old_val);
						}
						break;

					case 'priority':
						if (!$this->tracker->isNewTicket()) {
							$action = new LogActions\Priority($old_val, $new_val);
						}
						break;

					case 'workflow':
						if (!$this->tracker->isNewTicket()) {
							$action = new LogActions\Workflow($old_val, $new_val);
						}
						break;

					case 'product':
						if (!$this->tracker->isNewTicket()) {
							$action = new LogActions\Product($old_val, $new_val);
						}
						break;

					case 'status':
						if (!$this->tracker->isNewTicket()) {
							$action = new LogActions\Status($old_val, $new_val);
						}
						break;

					case 'urgency':
						if (!$this->tracker->isNewTicket()) {
							$action = new LogActions\Urgency($old_val, $new_val);
						}
						break;

					case 'person':
						if (!$this->tracker->isNewTicket()) {
							$action = new LogActions\Person($old_val, $new_val);
						}
						break;

					case 'organization':
						if (!$this->tracker->isNewTicket()) {
							$action = new LogActions\Organization($old_val, $new_val);
						}
						break;

					case 'subject':
						if (!$this->tracker->isNewTicket()) {
							$action = new LogActions\Subject($old_val, $new_val);
						}
						break;

					default:
						$unknown[] = $prop;
						break;
				}

				if ($action) {
					if (isset($info['trigger_id'])) {
						$action->setMetaData(array('trigger_id' => $info['trigger_id']));
					}
					$actions[] = $action;
				}
			}
		}

		if ($this->tracker->getChangedProperty('participants')) {
			foreach ($this->tracker->getChangedProperty('participants') as $info) {
				$old_val = null;
				$new_val = null;

				if (isset($info['old'])) $old_val = $info['old'];
				if (isset($info['new'])) $new_val = $info['new'];

				if ($old_val) {
					$action = new LogActions\ParticipantRemoved($old_val);
				} else {
					$action = new LogActions\ParticipantAdded($new_val);
				}

				if (isset($info['trigger_id'])) {
					$action->setMetaData(array('trigger_id' => $info['trigger_id']));
				}

				$actions[] = $action;
			}
		}

		if ($this->tracker->getChangedProperty('attachments')) {
			foreach ($this->tracker->getChangedProperty('attachments') as $info) {
				$old_val = null;
				$new_val = null;

				if (isset($info['old'])) $old_val = $info['old'];
				if (isset($info['new'])) $new_val = $info['new'];

				if ($old_val) {
					$action = new LogActions\AttachRemoved($old_val);
				} else {
					$action = new LogActions\AttachAdded($new_val);
				}

				if (isset($info['trigger_id'])) {
					$action->setMetaData(array('trigger_id' => $info['trigger_id']));
				}

				$actions[] = $action;
			}
		}

		if ($this->tracker->getChangedProperty('custom_data')) {
			foreach ($this->tracker->getChangedProperty('custom_data') as $info) {
				$old_val = null;
				$new_val = null;

				if (isset($info['old'])) $old_val = $info['old'];
				if (isset($info['new'])) $new_val = $info['new'];

				$action = new LogActions\CustomField($old_val, $new_val);

				if (isset($info['trigger_id'])) {
					$action->setMetaData(array('trigger_id' => $info['trigger_id']));
				}

				$actions[] = $action;
			}
		}

		// These are manually added log entries from elsewhere,
		// for example when sending emails.
		// $new_val contains:
		// - type: represents the action class
		// anywthing else is info for the action class to use
		$log_actions = $this->tracker->getChangedProperty('log_actions');
		if ($log_actions) {
			foreach ($log_actions as $log_action) {
				$info = $log_action['new'];

				$classname = ucfirst(Strings::underscoreToCamelCase($info['type']));
				$classname = 'Application\\DeskPRO\\Tickets\\TicketChangeInspector\\LogActions\\' . $classname;

				$action = new $classname($info);

				if (isset($info['trigger_id'])) {
					$action->setMetaData(array('trigger_id' => $info['trigger_id']));
				}

				$actions[] = $action;
			}
		}

		return $actions;
	}

	public function getTicketLogs()
	{
		$log_items = array();

		if ($this->tracker->isExtraSet('ticket_merge')) {
			$merge_info = $this->tracker->getExtra('ticket_merge');
			$action = new LogActions\Merge($this->ticket, $merge_info['other_ticket_id']);
			$l = $this->createNewTicketLog($action);
			if ($l) {
				$log_items[] = $l;
			}
		}

		if ($this->tracker->isExtraSet('ticket_split')) {
			$split_info = $this->tracker->getExtra('ticket_split');
			$action = new LogActions\Split($this->ticket, $split_info['old_ticket']);
			$l = $this->createNewTicketLog($action);
			if ($l) {
				$log_items[] = $l;
			}
		}

		if ($this->tracker->isExtraSet('ticket_created')) {
			$action = new LogActions\Created($this->ticket);
			$l = $this->createNewTicketLog($action);
			if ($l) {
				$log_items[] = $l;
			}
		}

		$log_actions = $this->getLogActions();
		foreach ($log_actions as $action) {
			$l = $this->createNewTicketLog($action);
			if ($l) {
				$log_items[] = $l;
			}
		}

		return $log_items;
	}

	public function run()
	{
		$this->tracker->logMessage('[Log] run');

		foreach ($this->getTicketLogs() as $log_item) {
			App::getOrm()->persist($log_item);
		}

		App::getOrm()->flush();
	}

	protected function createNewTicketLog(LogActionInterface $action)
	{
		$ticket_log = new Entity\TicketLog();
		$ticket_log['person'] = $this->getPersonContext();

		$ticket_log['ticket'] = $this->ticket;
		$ticket_log['action_type'] = $action->getLogName();
		$ticket_log['details'] = $action->getLogDetails();

		$metadata = $action->getMetaData();
		if (isset($metadata['trigger_id'])) {
			$ticket_log['trigger_id'] = $metadata['trigger_id'];
		}

		if ($ticket_log['details']) {
			return $ticket_log;
		}

		return null;
	}

	public function getPersonContext()
	{
		if ($this->person_context !== -1) {
			return $this->person_context;
		}

		$this->person_context = App::getCurrentPerson();
		if (!$this->person_context || !$this->person_context->getId()) {
			// If we're in a gateway, see if we've added a reply, and the person who added the reply will be our context.
			if (DP_INTERFACE == 'cli') {
				if ($this->tracker->isExtraSet('ticket_created')) {
					$this->person_context = $this->ticket->person;
				} elseif ($this->tracker->hasNewReply()) {
					$this->person_context = $this->tracker->getNewReply()->person;
				}
			}
		}

		if (!$this->person_context || !$this->person_context->getId()) {
			$this->person_context = null;
		}

		return $this->person_context;
	}
}

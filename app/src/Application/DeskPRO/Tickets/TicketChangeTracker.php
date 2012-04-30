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

namespace Application\DeskPRO\Tickets;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity;

use Orb\Util\Arrays;

/**
 * The ticket listener listens for changes to a ticket, and then runs inspections once the changes
 * are committed.
 *
 * This is mostly an intermediary event dispatcher that records a batch of changes, and then notifies
 * listeners at the end when the changes are committed. This allows listeners to inspect the full
 * batch of changes to decide what to do (ex trigger criteria etc).
 */
class TicketChangeTracker extends \Application\DeskPRO\Domain\ChangeTracker
{
	protected $ticket;
	protected $original_ticket = null;
	protected $is_new_ticket = false;

	protected $log_inspector;
	protected $exec_inspector;
	protected $list_updater;
	protected $search_updater;
	protected $filter_detector;
	protected $notify_list_builder;

	protected $has_non_ignored = false;
	protected $running = false;

	/**
	 * Fields that shouldnt trigger the full logger and filter inspections
	 * THey are still recoreded and may still be used as criteria, but they are always
	 * accompanied by a real trigger such as a status change etc. So by themselves
	 * they dont trigger inspections.
	 *
	 * (Really the only one that needs to be here is date_locked since the others are never
	 * actually set in code.)
	 *
	 * @var array
	 */
	public static $ignored_fields = array(
		'id', 'ref', 'auth', 'attachments', 'access_codes', 'email_gateway', 'ticket_hash',
		'date_created', 'date_resolved', 'date_closed', 'date_closed', 'date_first_agent_assign',
		'date_first_agent_reply', 'date_last_agent_reply', 'date_last_user_reply',
		'date_agent_waiting', 'date_user_waiting', 'total_user_waiting', 'total_to_first_reply',
		'locked_by_agent', 'date_locked', 'has_attachments',
	);

	protected $log;

	protected $start_time;

	public function __construct(Entity\Ticket $ticket)
	{
		$this->entity = $ticket;
		$this->ticket = $ticket;

		$this->person_context = App::getCurrentPerson();

		if (!$ticket['id']) {
			$this->is_new_ticket = true;
		}
	}

	public function getPersonPerformer()
	{
		return $this->person_context;
	}

	public function getLog()
	{
		if ($this->log) return $this->log;
		if (App::getConfig('debug.ticket_change_logger')) {
			$logger = new \Orb\Log\Logger();
			$writer = new \Orb\Log\Writer\Stream(App::getContainer()->getLogDir() . '/ticket-change-tracker.log');
			$logger->addWriter($writer);

			if (DP_INTERFACE == 'cli') {
				$writer = new \Orb\Log\Writer\Output();
				$logger->addWriter($writer);
			}

			$this->log = $logger;
		}
		return $this->log;
	}

	public function logMessage($message)
	{
		$this->getLog();
		if ($this->log) {
			$this->log->log($message, \Orb\Log\Logger::DEBUG);
		}
	}


	/**
	 * Checks if this ticket is new, or should be TREATED as new.
	 * A ticket should be treated as new when it comes out of validation.
	 *
	 * @return bool
	 */
	public function isNewTicket()
	{
		$status_change  = $this->getChangedProperty('status');
		$hstatus_change = $this->getChangedProperty('hidden_status');

		if ($this->isExtraSet('ticket_created') || ($this->ticket->status_code == 'awaiting_agent' && ($status_change['old'] == 'hidden' && $hstatus_change['old'] == 'validating'))) {
			return true;
		}

		return false;
	}

	/**
	 * Check if a new agent reply was added
	 *
	 * @return bool
	 */
	public function hasNewAgentReply()
	{
		$messages = $this->getChangedProperty('messages');
		if ($messages) {
			$message = array_shift($messages);
			$message = $message['new'];
			if ($message->person->is_agent) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Get the new agent reply just added
	 *
	 * @return \Application\DeskPRO\Entity\TicketMessage
	 */
	public function getNewAgentReply()
	{
		$messages = $this->getChangedProperty('messages');
		if ($messages) {
			$message = array_shift($messages);
			$message = $message['new'];
			if ($message->person->is_agent) {
				return $message;
			}
		}

		return null;
	}

	/**
	 * Get the new agent reply just added
	 *
	 * @return \Application\DeskPRO\Entity\TicketMessage
	 */
	public function getNewUserReply()
	{
		$messages = $this->getChangedProperty('messages');
		if ($messages) {
			$message = array_shift($messages);
			$message = $message['new'];
			if (!$message->person->is_agent) {
				return $message;
			}
		}

		return null;
	}


	/**
	 * Check if a new user reply was added
	 *
	 * @return bool
	 */
	public function hasNewUserReply()
	{
		$messages = $this->getChangedProperty('messages');
		if ($messages) {
			$message = array_shift($messages);
			$message = $message['new'];
			if (!$message->person->is_agent) {
				return true;
			}
		}

		return false;
	}


	/**
	 * Get the ticket
	 *
	 * @return \Application\DeskPRO\Entity\Ticket
	 */
	public function getTicket()
	{
		return $this->ticket;
	}


	/**
	 * Get the original ticket before changes, used for comparisons usually
	 *
	 * @return \Application\DeskPRO\Entity\Ticket
	 */
	public  function getOriginalTicket()
	{
		/*
		 * Manually reconstructing the original ticket based off of the changelog.
		 * Easier would have been to clone the ticket in __construct, but because
		 * this tracker is created in Doctrine's PostLoad event, object references
		 * are set up yet and we'd end up with all these relationships being 0.
		 * (Which was what happened the first time I wrote the functionality!)
		 */

		if ($this->original_ticket !== null) return $this->original_ticket;

		$this->original_ticket = clone $this->ticket;

		foreach ($this->getAllChangedProperties() as $prop => $info) {
			$action = null;

			$old_val = null;

			if (isset($info['old'])) $old_val = $info['old'];

			switch ($prop) {
				case 'agent':
					$this->original_ticket['agent'] = $old_val;
					break;

				case 'agent_team':
					$this->original_ticket['agent_team'] = $old_val;
					break;

				case 'person':
					if (!$this->is_new_ticket) {
						$this->original_ticket['person'] = $old_val;
					}
					break;

				case 'category':
					$this->original_ticket['category'] = $old_val;
					break;

				case 'department':
					$this->original_ticket['department'] = $old_val;
					break;

				case 'priority':
					$this->original_ticket['priority'] = $old_val;
					break;

				case 'product':
					$this->original_ticket['product'] = $old_val;
					break;

				case 'workflow':
					$this->original_ticket['workflow'] = $old_val;
					break;

				case 'status':
					$this->original_ticket['status'] = $old_val;
					break;

				case 'subject':
					$this->original_ticket['subject'] = $old_val;
					break;

				case 'is_hold':
					$this->original_ticket['is_hold'] = $old_val;
					break;

				case 'hidden_status':
					$this->original_ticket['hidden_status'] = $old_val;
					break;
			}
		}

		return $this->original_ticket;
	}


	public function propertyChanged($sender, $prop, $old_val, $new_val)
	{
		if (!in_array($prop, self::$ignored_fields)) {
			$this->has_non_ignored = true;
		}

		if (in_array($prop, array('messages'))) {
			$this->recordMultiPropertyChanged($prop, $old_val, $new_val);
		} else {
			$this->recordPropertyChanged($prop, $old_val, $new_val);
		}
	}

	public function recordPropertyChanged($prop, $old_val, $new_val)
	{
		$this->has_non_ignored = true;
		parent::recordPropertyChanged($prop, $old_val, $new_val);
	}

	public function recordMultiPropertyChanged($prop, $old_val, $new_val)
	{
		$this->has_non_ignored = true;
		parent::recordMultiPropertyChanged($prop, $old_val, $new_val);
	}

	/**
	 * Service to fetch information about how this change affected various filters.
	 *
	 * @return \Application\DeskPRO\Tickets\TicketChangeInspector\DetectFilterMatches
	 */
	public function getFilterDetector()
	{
		if ($this->filter_detector !== null) return $this->filter_detector;

		$this->logMessage('[TicketChangeTracker] init filter_detector');

		$this->filter_detector = new TicketChangeInspector\DetectFilterMatches($this);
		return $this->filter_detector;
	}


	/**
	 * Service to generate lists of who should be notified based off of preferences and
	 * how filters were affected.
	 *
	 * @return \Application\DeskPRO\Tickets\TicketChangeInspector\NotifyListBuilder;
	 */
	public function getNotifyListBuilder()
	{
		if ($this->notify_list_builder !== null) return $this->notify_list_builder;
		$this->notify_list_builder = new TicketChangeInspector\NotifyListBuilder($this, $this->getFilterDetector());

		$this->logMessage('[TicketChangeTracker] init notify_list_builder');

		return $this->notify_list_builder;
	}


	/**
	 * @return \Application\DeskPRO\Tickets\TicketChangeInspector\Log
	 */
	public function getLogInspector()
	{
		if ($this->log_inspector !== null) return $this->log_inspector;

		$this->logMessage('[TicketChangeTracker] init log_inspector');

		$this->log_inspector = new TicketChangeInspector\Log($this);
		return $this->log_inspector;
	}


	/**
	 * @return \Application\DeskPRO\Tickets\TicketChangeInspector\TriggerExecutor
	 */
	public function getTriggerExecutorInspector()
	{
		if ($this->exec_inspector !== null) return $this->exec_inspector;

		$this->logMessage('[TicketChangeTracker] init exec_inspector');

		$this->exec_inspector = new TicketChangeInspector\TriggerExecutor($this);
		return $this->exec_inspector;
	}


	/**
	 * @return \Application\DeskPRO\Tickets\TicketChangeInspector\ListUpdater
	 */
	public  function getListUpdater()
	{
		if ($this->list_updater !== null) return $this->list_updater;

		$this->logMessage('[TicketChangeTracker] init list_updater');

		$this->list_updater = new TicketChangeInspector\ListUpdater($this, $this->getFilterDetector());
		return $this->list_updater;
	}


	/**
	 * @return \Application\DeskPRO\Tickets\TicketChangeInspector\SearchUpdater
	 */
	public function getSearchUpdater()
	{
		if ($this->search_updater !== null) return $this->search_updater;

		$this->search_updater = new TicketChangeInspector\SearchUpdater($this);
		return $this->search_updater;
	}


	/**
	 * Notify all listeners that changes are about to be committed
	 */
	public function preDone()
	{
		if (!$this->has_non_ignored) {
			return;
		}
		$this->logMessage("[TicketChangeTracker] BEGIN TICKET {$this->ticket['id']}");
		$this->start_time = microtime(true);
		$this->getLogInspector()->runPre();
	}



	/**
	 * Notify all listeners that changes to the ticket have been committed
	 */
	public function done()
	{
		if ($this->running) {
			$this->logMessage('[TicketChangeTracker] (running)');
			return;
		}
		if (!$this->has_non_ignored && !$this->isExtraSet('always_run_tracker')) {
			$this->logMessage('[TicketChangeTracker] (trivial change set)');
			return;
		}

		$this->running = true;

		$this->logMessage('[TicketChangeTracker] done');

		$this->ticket->unsetTicketLogger();

		$this->getTriggerExecutorInspector()->runPre();
		$this->getListUpdater()->run();
		$this->getTriggerExecutorInspector()->run();
		$this->getLogInspector()->run();

		$person_activity = new \Application\DeskPRO\Tickets\TicketChangeInspector\PersonActivity($this);
		$person_activity->run();

		$total_time = microtime(true) - $this->start_time;
		$this->logMessage("[TicketChangeTracker] END TICKET {$this->ticket['id']} : Took " . $total_time . " seconds");

		$this->getSearchUpdater()->run();

		$this->running = false;
	}
}

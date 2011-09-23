<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @category Tickets
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
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

	public function __construct(Entity\Ticket $ticket)
	{
		$this->entity = $ticket;
		$this->ticket = $ticket;

		if (!$ticket['id']) {
			$this->is_new_ticket = true;
		}
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

				case 'status':
					$this->original_ticket['status'] = $old_val;
					break;

				case 'hidden_status':
					$this->original_ticket['hidden_status'] = $old_val;
					break;
			}
		}

		return $this->original_ticket;
	}


	/**
	 * Was the ticket new (just created?)
	 *
	 * @return bool
	 */
	public function isNewTicket()
	{
		return $this->is_new_ticket;
	}



	public function propertyChanged($sender, $prop, $old_val, $new_val)
	{
		if (in_array($prop, array('messages'))) {
			$this->recordMultiPropertyChanged($prop, $old_val, $new_val);
		} else {
			$this->recordPropertyChanged($prop, $old_val, $new_val);
		}
	}


	/**
	 * @return \Application\DeskPRO\Tickets\TicketChangeInspector\Log
	 */
	public function getLogInspector()
	{
		if ($this->log_inspector !== null) return $this->log_inspector;
		$this->log_inspector = new TicketChangeInspector\Log($this);;
		return $this->log_inspector;
	}


	/**
	 * @return \Application\DeskPRO\Tickets\TicketChangeInspector\TriggerExecutor
	 */
	public function getTriggerExecutorInspector()
	{
		if ($this->exec_inspector !== null) return $this->exec_inspector;
		$this->exec_inspector = new TicketChangeInspector\TriggerExecutor($this);
		return $this->exec_inspector;
	}


	/**
	 * @return \Application\DeskPRO\Tickets\TicketChangeInspector\ListUpdater
	 */
	public  function getListUpdater()
	{
		if ($this->list_updater !== null) return $this->list_updater;

		$this->list_updater = new TicketChangeInspector\ListUpdater($this, 'check');
		return $this->list_updater;
	}


	/**
	 * Notify all listeners that changes are about to be committed
	 */
	public function preDone()
	{
		$this->getLogInspector()->runPre();
	}



	/**
	 * Notify all listeners that changes to the ticket have been committed
	 */
	public function done()
	{
		$this->getListUpdater()->run();
		$this->getTriggerExecutorInspector()->run();
		$this->getLogInspector()->run();

		$person_activity = new \Application\DeskPRO\Tickets\TicketChangeInspector\PersonActivity($this);
		$person_activity->run();
	}
}

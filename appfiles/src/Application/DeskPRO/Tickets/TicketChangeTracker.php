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

use \Application\DeskPRO\App;
use \Application\DeskPRO\Entity;

use \Orb\Util\Arrays;

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
	protected $is_new_ticket = false;

	protected $log_inspector;
	protected $exec_inspector;

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
	 */
	public function getTicket()
	{
		return $this->ticket;
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
		$this->getLogInspector()->run();
		$this->getTriggerExecutorInspector()->run();
	}
}
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
class TicketChangeTracker implements \Doctrine\Common\PropertyChangedListener
{
	protected $ticket;
	protected $is_new_ticket = false;
	protected $changes = array();
	protected $extra = array();

	public function __construct(Entity\Ticket $ticket)
	{
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
	 * Log a property change
	 *
	 * @param  $prop
	 * @param  $old_val
	 * @param  $new_val
	 */
	public function recordPropertyChanged($prop, $old_val, $new_val)
	{
		$this->changes[$prop] = array('old' => $old_val, 'new' => $new_val);
	}



	/**
	 * Log a property change where the value is multiple, such as additions to a collection
	 *
	 * @param  $prop
	 * @param  $old_val
	 * @param  $new_val
	 */
	public function recordMultiPropertyChanged($prop, $old_val, $new_val)
	{
		if (!isset($this->changes[$prop])) $this->changes[$prop] = array();

		$this->changes[$prop][] = array('old' => $old_val, 'new' => $new_val);
	}



	/**
	 * Get details of a property change
	 * 
	 * @param  $prop
	 * @return array|null
	 */
	public function getChangedProperty($prop)
	{
		return isset($this->changes[$prop]) ? $this->changes[$prop] : null;
	}



	/**
	 * Get array of all property changes
	 *
	 * @return array
	 */
	public function getAllChangedProperties()
	{
		return $this->changes;
	}



	/**
	 * Get the names of all changed properties
	 * 
	 * @return array
	 */
	public function getAllChangedPropertyNames()
	{
		return array_keys($this->changes);
	}



	/**
	 * Check if a specific property is changed
	 * 
	 * @param  $prop
	 * @return bool
	 */
	public function isPropertyChanged($prop)
	{
		return isset($this->changes[$prop]);
	}



	/**
	 * Record some extra data about a ticket event that listeners might be interested in
	 *
	 * @param  $key
	 * @param  $value
	 */
	public function recordExtra($key, $value)
	{
		$this->extra[$key] = $value;
	}



	/**
	 * Get extra data
	 * 
	 * @param  $key
	 * @return array|null
	 */
	public function getExtra($key)
	{
		return isset($this->extra[$key]) ? $this->extra[$key] : null;
	}



	/**
	 * Get an array of all registered extra data
	 * 
	 * @return array
	 */
	public function getAllExtra()
	{
		return $this->extra;
	}



	/**
	 * Check if some extra data item is set
	 *
	 * @param  $key
	 * @return bool
	 */
	public function isExtraSet($key)
	{
		return isset($this->extra[$key]);
	}



	/**
	 * Notify all listeners that changes to the ticket have been committed
	 *
	 * @return void
	 */
	public function done()
	{
		$log_inspector = new TicketChangeInspector\Log($this);
		$log_inspector->run();

		$log_inspector = new TicketChangeInspector\Triggers($this);
		$log_inspector->run();
	}
}
<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage Tickets
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */
namespace Application\DeskPRO\Tickets;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity\Ticket;
use Orb\Util\Arrays;

class TicketResultsDisplay
{
	/**
	 * @var \Application\DeskPRO\Entity\Ticket[]
	 */
	protected $tickets;

	/**
	 * @var array
	 */
	protected $ticket_ids;

	/**
	 * @var \Doctrine\ORM\EntityManager
	 */
	protected $em;

	/**
	 * @var \Application\DeskPRO\DBAL\Connection
	 */
	protected $db;

	/**
	 * @var int
	 */
	protected $ticket_count;

	/**
	 * @var array
	 */
	protected $all_labels;

	/**
	 * @param \Application\DeskPRO\Entity\Ticket[] $tickets
	 */
	public function __construct(array $tickets)
	{
		$this->tickets = $tickets;
		$this->ticket_count = count($tickets);
		$this->ticket_ids = Arrays::flattenToIndex($this->tickets, 'id');


		$this->em = App::getOrm();
		$this->db = $this->em->getConnection();
	}


	/**
	 * @return int
	 */
	public function getCount()
	{
		return $this->ticket_count;
	}


	/**
	 * @return \Application\DeskPRO\Entity\Ticket[]
	 */
	public function getTickets()
	{
		return $this->tickets;
	}


	/**
	 * @return array
	 */
	public function getAllLabels()
	{
		if ($this->all_labels !== null) return $this->all_labels;

		if (!$this->ticket_count) {
			$this->all_labels = array();
			return $this->all_labels;
		}

		$ticket_ids = implode(',', $this->ticket_ids);

		$this->all_labels = $this->db->fetchAllGrouped("
			SELECT ticket_id, label
			FROM labels_tickets
			WHERE ticket_id IN ($ticket_ids)
		", array(), 'ticket_id', null, 'label');

		return $this->all_labels;
	}


	/**
	 * Get an array of labels applied to a ticket
	 *
	 * @param \Application\DeskPRO\Entity\Ticket $ticket
	 * @return array
	 */
	public function getTicketLabels(Ticket $ticket)
	{
		$this->getAllLabels();
		return empty($this->all_labels[$ticket->id]) ? array() : $this->all_labels[$ticket->id];
	}


	/**
	 * Check if a ticket has labels
	 *
	 * @param \Application\DeskPRO\Entity\Ticket $ticket
	 * @return bool
	 */
	public function hasTicketLabels(Ticket $ticket)
	{
		$this->getAllLabels();
		return !empty($this->all_labels[$ticket->id]);
	}
}

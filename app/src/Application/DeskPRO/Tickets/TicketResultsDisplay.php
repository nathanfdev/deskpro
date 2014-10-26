<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/  |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2014, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at https://www.deskpro.com/eula/                            |
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
namespace Application\DeskPRO\Tickets;

use Application\DeskPRO\App;
use Application\DeskPRO\DBAL\Connection;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\People\PersonContextInterface;
use Orb\Util\Arrays;
use Orb\Util\Strings;

class TicketResultsDisplay implements PersonContextInterface
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
	 * @var array
	 */
	protected $all_ticket_slas;

	/**
	 * @var array
	 */
	protected $all_previews;

	/**
	 * @var array
	 */
	protected $people;

	/**
	 * @var array
	 */
	protected $people_ids;

	/**
	 * @var \Application\DeskPRO\Entity\Person
	 */
	protected $person_context;

	/**
	 * @var array
	 */
	protected $person_flagged;

	/**
	 * @var array
	 */
	protected $all_ticket_field_data;

	/**
	 * @var array
	 */
	protected $all_user_field_data;

	/**
	 * @var array
	 */
	protected $all_org_field_data;

	public function setPersonContext(Person $person)
	{
		$this->person_context = $person;
	}

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

		$people_ids = array();
		foreach ($tickets as $ticket) {
			$people_ids[] = $ticket->person->getId();
			if ($ticket->agent) {
				$people_ids[] = $ticket->agent->getId();
			}
		}

		$this->dep_names = App::getDataService('Department')->getFullNames();
		$this->people = App::getDataService('Person')->getPeopleResultsFromIds($people_ids);
		$this->people_ids = $people_ids;
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
	 * @return array
	 */
	public function getAllUserFieldData()
	{
		if ($this->all_user_field_data !== null) return $this->all_user_field_data;
		$data = $this->em->createQuery("
			SELECT d, def, root_def
			FROM DeskPRO:CustomDataPerson AS d
			LEFT JOIN d.field def
			LEFT JOIN d.root_field root_def
			WHERE d.person IN (?0)
		")->execute(array(array_values($this->people_ids)));

		$this->all_user_field_data = array();
		foreach ($data as $d) {
			$tid = $d->person->getId();
			if (!isset($this->all_user_field_data[$tid])) {
				$this->all_user_field_data[$tid] = array();
			}

			$this->all_user_field_data[$tid][] = $d;
		}

		return $this->all_user_field_data;
	}


	/**
	 * @param Ticket $ticket
	 * @return array
	 */
	public function getUserFieldData(Person $person)
	{
		$this->getAllUserFieldData();
		return isset($this->all_user_field_data[$person->getId()]) ? $this->all_user_field_data[$person->getId()] : array();
	}


	/**
	 * @return array
	 */
	public function getAllTicketFieldData()
	{
		if ($this->all_ticket_field_data !== null) return $this->all_ticket_field_data;
		$data = $this->em->createQuery("
			SELECT d, def, root_def
			FROM DeskPRO:CustomDataTicket AS d
			LEFT JOIN d.field def
			LEFT JOIN d.root_field root_def
			WHERE d.ticket IN (?0)
		")->execute(array(array_values($this->ticket_ids)));

		$this->all_ticket_field_data = array();
		foreach ($data as $d) {
			$tid = $d->ticket->getId();
			if (!isset($this->all_ticket_field_data[$tid])) {
				$this->all_ticket_field_data[$tid] = array();
			}

			$this->all_ticket_field_data[$tid][] = $d;
		}

		return $this->all_ticket_field_data;
	}


	/**
	 * @param Ticket $ticket
	 * @return array
	 */
	public function getTicketFieldData(Ticket $ticket)
	{
		$this->getAllTicketFieldData();
		return isset($this->all_ticket_field_data[$ticket->id]) ? $this->all_ticket_field_data[$ticket->id] : array();
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

	/**
	 * @return array
	 */
	public function getAllTicketSlas()
	{
		if ($this->all_ticket_slas !== null) return $this->all_ticket_slas;

		if (!$this->ticket_count) {
			$this->all_ticket_slas = array();
			return $this->all_ticket_slas;
		}

		$ticket_ids = implode(',', $this->ticket_ids);

		$this->all_ticket_slas = $this->db->fetchAllGrouped("
			SELECT ticket_slas.*, slas.title
			FROM ticket_slas
			INNER JOIN slas ON (ticket_slas.sla_id = slas.id)
			WHERE ticket_slas.ticket_id IN ($ticket_ids)
				AND ticket_slas.is_completed = 0
		", array(), 'ticket_id', 'id');

		return $this->all_ticket_slas;
	}


	/**
	 * Get an array of labels applied to a ticket
	 *
	 * @param \Application\DeskPRO\Entity\Ticket $ticket
	 * @return array
	 */
	public function getTicketSlas(Ticket $ticket)
	{
		$this->getAllTicketSlas();
		return empty($this->all_ticket_slas[$ticket->id]) ? array() : $this->all_ticket_slas[$ticket->id];
	}

	/**
	 * Check if a ticket has an SLA
	 *
	 * @param \Application\DeskPRO\Entity\Ticket $ticket
	 * @return bool
	 */
	public function hasTicketSlas(Ticket $ticket)
	{
		$this->getAllTicketSlas();
		return !empty($this->all_ticket_slas[$ticket->id]);
	}

	public function getNextSlaTriggerDate(array $ticket_sla)
	{
		$times = array();

		if ($ticket_sla['sla_status'] == 'ok' && $ticket_sla['warn_date']) {
			$time = new \DateTime($ticket_sla['warn_date'], new \DateTimeZone('UTC'));
			if ($time->getTimestamp() > time() || !$ticket_sla['fail_date']) {
				$times[] = $time->getTimestamp();
			}
		}

		if ($ticket_sla['fail_date']) {
			$time = new \DateTime($ticket_sla['fail_date'], new \DateTimeZone('UTC'));
			$times[] = $time->getTimestamp();
		}

		if (!$times) {
			return null;
		}

		return new \DateTime('@' . min($times));
	}


	/**
	 * @param \Application\DeskPRO\Entity\Ticket $ticket
	 * @return \Application\DeskPRO\Entity\Person
	 */
	public function getPerson(Ticket $ticket)
	{
		if (!$ticket->person) {
			return null;
		}

		return $this->people[$ticket->person->getId()];
	}


	/**
	 * @param \Application\DeskPRO\Entity\Ticket $ticket
	 * @return \Application\DeskPRO\Entity\Person
	 */
	public function getAgent(Ticket $ticket)
	{
		if (!$ticket->agent) {
			return null;
		}

		return $this->people[$ticket->agent->getId()];
	}


	/**
	 * @param \Application\DeskPRO\Entity\Ticket $ticket
	 * @return string
	 */
	public function getDepartmentName(Ticket $ticket)
	{
		if (!$ticket->department) {
			return null;
		}

		if (!isset($this->dep_names[$ticket->department->getId()])) {
			return '';
		}

		return $this->dep_names[$ticket->department->getId()];
	}


	/**
	 * Gets array of previews for each ticket
	 * @return array
	 */
	public function getAllTicketPreviews()
	{
		if ($this->all_previews !== null) return $this->all_previews;

		if (!$this->ticket_ids) {
			$this->all_previews = array();
			return $this->all_previews;
		}

		$message_data = $this->db->fetchAllKeyed("
			SELECT
				tickets_messages.id, tickets_messages.ticket_id, tickets_messages.date_created, tickets_messages.message,
				people.id AS person_id, people.name, people.first_name, people.last_name, people.is_agent,
				tickets_messages.is_agent_note
			FROM tickets_messages
			LEFT JOIN people ON (people.id = tickets_messages.person_id)
			WHERE tickets_messages.ticket_id IN (?)
			ORDER BY tickets_messages.id DESC
		", array($this->ticket_ids), 'id', array(Connection::PARAM_INT_ARRAY));

		$this->all_previews = array();
		foreach ($message_data as $m) {
			if (!isset($this->all_previews[$m['ticket_id']])) {
				$this->all_previews[$m['ticket_id']] = array();
			}

			$m['status'] = $m['is_agent_note']
				? 'wrote a note'
				: ($this->tickets[$m['ticket_id']]->date_created->format('Y-m-d H:i:s') === $m['date_created']
					? 'created ticket'
					: 'replied'
				);

			$m['date_created'] = \DateTime::createFromFormat('Y-m-d H:i:s', $m['date_created']);

			if ($m['first_name'] && $m['last_name']) {
				$m['display_name'] = $m['first_name'] . ' ' . $m['last_name'];
			} else if ($m['name']) {
				$m['display_name'] = $m['name'];
			} else if ($m['last_name']) {
				$m['display_name'] = $m['last_name'];
			} else if ($m['first_name']) {
				$m['display_name'] = $m['first_name'];
			} else {
				$m['display_name'] = 'User';
			}



			$m['preview_text'] = $this->_getMessagePreviewText($m['message'], 750);
			$m['picture_url_16'] = isset($this->people[$m['person_id']])
				? $this->people[$m['person_id']]->getPictureUrl(16)
				: null;

			$this->all_previews[$m['ticket_id']][] = $m;
		}

		return $this->all_previews;
	}

	private function _getMessagePreviewText($message, $max_length = 0, $ellipses = '...')
	{
		$message = preg_replace('#\[attach:([a-zA-Z0-9\-_\.]+):([a-zA-Z0-9\-_\.]+):([a-zA-Z0-9\-_\. ]+)\]#', '', $message);

		$sig_pos = strpos($message, '<div class="dp-signature-start">');

		if ($sig_pos !== false) {
			$message = substr($message, 0, $sig_pos);
		}

		$message = Strings::standardEol($message);
		$message = str_replace(array('<br/>', '<br>', '<br />', '<p>', '</p>'), "\n", $message);
		$message = strip_tags($message);
		$message = Strings::decodeHtmlEntities($message);
		$message = preg_replace('#\s+#', ' ', $message);
		$message = trim($message);

		if ($max_length && isset($message[$max_length])) {
			$message = substr($message, 0, $max_length);
			$message = trim($message);
			$message .= $ellipses;
		}

		return $message;
	}



	/**
	 * Get an array of ticket message previews
	 *
	 * @param Ticket $ticket
	 * @return array
	 */
	public function getTicketPreview($ticket)
	{
		$this->getAllTicketPreviews();

		if (isset($this->all_previews[$ticket->id])) {
			return $this->all_previews[$ticket->id];
		}

		return array();
	}

	/**
	 * @param mixed $ticket
	 * @return string
	 */
	public function getFlaggedColor($ticket)
	{
		if (!$this->person_context) {
			return null;
		}

		if ($this->person_flagged === null) {
			$this->person_flagged = App::getDb()->fetchAllKeyValue("
				SELECT ticket_id, color
				FROM tickets_flagged
				WHERE person_id = ? AND ticket_id IN (?)"
			, array($this->person_context->getId(), $this->ticket_ids), array(\PDO::PARAM_INT, Connection::PARAM_INT_ARRAY));
		}

		$ticket_id = is_object($ticket) ? $ticket->getId() : $ticket;

		return isset($this->person_flagged[$ticket_id]) ? $this->person_flagged[$ticket_id] : null;
	}
}

<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage People
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */
namespace Application\DeskPRO\People;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity\Person;
use Orb\Util\Arrays;

class PeopleResultsDisplay
{
	/**
	 * @var \Application\DeskPRO\Entity\Person[]
	 */
	protected $people;

	/**
	 * @var array
	 */
	protected $people_ids;

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
	protected $people_count;

	/**
	 * @var array
	 */
	protected $all_labels;

	/**
	 * @var array
	 */
	protected $people_ticket_counts;

	/**
	 * @param \Application\DeskPRO\Entity\People[] $people
	 */
	public function __construct(array $people)
	{
		$this->people = $people;
		$this->people_count = count($people);
		$this->people_ids = Arrays::flattenToIndex($this->people, 'id');


		$this->em = App::getOrm();
		$this->db = $this->em->getConnection();
	}


	/**
	 * @return int
	 */
	public function getCount()
	{
		return $this->people_count;
	}


	/**
	 * @return \Application\DeskPRO\Entity\Person[]
	 */
	public function getpeople()
	{
		return $this->people;
	}


	/**
	 * @return array
	 */
	public function getAllLabels()
	{
		if ($this->all_labels !== null) return $this->all_labels;

		if (!$this->people_count) {
			$this->all_labels = array();
			return $this->all_labels;
		}

		$people_ids = implode(',', $this->people_ids);

		$this->all_labels = $this->db->fetchAllGrouped("
			SELECT person_id, label
			FROM labels_people
			WHERE person_id IN ($people_ids)
		", array(), 'person_id', null, 'label');

		return $this->all_labels;
	}


	/**
	 * Get an array of labels applied to a person
	 *
	 * @param \Application\DeskPRO\Entity\Person $person
	 * @return array
	 */
	public function getPersonLabels(Person $person)
	{
		$this->getAllLabels();
		return empty($this->all_labels[$person->id]) ? array() : $this->all_labels[$person->id];
	}


	/**
	 * Check if a person has labels
	 *
	 * @param \Application\DeskPRO\Entity\Person $person
	 * @return bool
	 */
	public function hasPersonLabels(Person $person)
	{
		$this->getAllLabels();
		return !empty($this->all_labels[$person->id]);
	}


	/**
	 * @return array
	 */
	public function getAllPeopleTicketCounts()
	{
		if ($this->people_ticket_counts !== null) return $this->people_ticket_counts;

		$this->people_ticket_counts = $this->em->getRepository('DeskPRO:Ticket')->getTicketCountsForPeople($this->people);

		return $this->people_ticket_counts;
	}


	/**
	 * Get the number of tickets submitted by a user.
	 *
	 * @param \Application\DeskPRO\Entity\Person $person
	 * @return int
	 */
	public function getPersonTicketCount(Person $person)
	{
		return isset($this->people_ticket_counts[$person->id]) ? $this->people_ticket_counts[$person->id] : 0;
	}
}

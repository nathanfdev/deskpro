<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @category Entities
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\DeskPRO\EntityRepository;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity;

use Orb\Util\Arrays;
use Orb\Util\Numbers;

class Ticket extends AbstractEntityRepository
{
	/**
	 * Find a ticket by its TAC
	 *
	 * @param $access_code
	 * @return null
	 */
	public function getByAccessCode($access_code)
	{
		$info = Entity\Ticket::decodeAccessCode($access_code);
		if (!$info) {
			return null;
		}

		try {
			$rec = $this->getEntityManager()->createQuery("
				SELECT t
				FROM DeskPRO:Ticket t
				WHERE t.id = :ticket_id AND t.auth = :auth
			")->setParameters($info)->setMaxResults(1)->getSingleResult();

			return $rec;
		} catch (\Doctrine\ORM\NoResultException $e) {
			return null;
		}
	}


	/**
	 * Get tickets by specific ids
	 *
	 * TODO: Verify agent permissions with $person_context if supplied
	 *
	 * @param array $ids
	 * @return array
	 */
	public function getTicketsFromIds(array $ids, Entity\Person $person_context = null)
	{
		// Only valid ID's please :)
		// Do this because Doctrine doesnt have proper IN()
		// escaping until 2.1
		$ids = array_filter($ids, function ($val) {
			if (Numbers::isInteger($val)) {
				return true;
			}
			return false;
		});

		if (!$ids) return array();

		$tickets = $this->getEntityManager()->createQuery("
			SELECT t
			FROM DeskPRO:Ticket t INDEX BY t.id
			WHERE t.id IN(" . implode(',', $ids) . ")
			ORDER BY t.id ASC
		")->execute();

		return $tickets;
	}

	/**
	 * Get all tickets a person owns, or is a participant in.
	 * This is usually used to fetch a list of tickets for an end-user.
	 *
	 * @return array
	 */
	public function getPersonTickets(Entity\Person $person, $limit = null)
	{
		$tickets = $this->getEntityManager()->createQuery("
			SELECT t
			FROM DeskPRO:Ticket t INDEX BY t.id
			LEFT JOIN t.participants p
			WHERE t.person = ?1 OR p.person = ?2
			ORDER BY t.id DESC
		")->setParameters(array(1=>$person, 2=>$person))->setMaxResults($limit)->execute();

		return $tickets;
	}


	/**
	 * Get tickets for any of an array of people
	 *
	 * @param array $people
	 * @param null $limit
	 * @return array
	 */
	public function getTicketsForPeople(array $people, $limit = null)
	{
		$ids = array();
		foreach ($people as $p) {
			if (is_object($p)) {
				$ids[] = $p->id;
			} else {
				$ids[] = $p;
			}
		}

		$ids = array_unique($ids);
		$ids = Arrays::removeFalsey($ids);

		if (!$ids) {
			return array();
		}

		$tickets = $this->getEntityManager()->createQuery("
			SELECT t
			FROM DeskPRO:Ticket t INDEX BY t.id
			LEFT JOIN t.participants p
			WHERE t.person IN (".implode(',', $ids).") OR p.person IN (".implode(',', $ids).")
			ORDER BY t.id DESC
		")->setMaxResults($limit)->execute();

		return $tickets;
	}


	/**
	 * Count how many tickets a person has
	 *
	 * @param \Application\DeskPRO\Entity\Person $person
	 * @param null $status
	 * @return int
	 */
	public function countTicketsForPerson(Entity\Person $person, $status = null)
	{
		if ($status) {
			$status = (array)$status;
			foreach ($status as &$s) {
				$s = "'$s'";
			}
			$status = implode(',', $status);
		}

		$count = App::getDb()->fetchColumn("
			SELECT COUNT(*)
			FROM tickets
			WHERE person_id = ? " . ($status ? " AND status IN ($status) " : '') . "
		", array($person['id']));

		return $count;
	}


	/**
	 * Get all tickets that belong ot an org
	 *
	 * @return array
	 */
	public function getOrganizationTickets(Entity\Organization $org, $limit = null)
	{
		$tickets = $this->getEntityManager()->createQuery("
			SELECT t
			FROM DeskPRO:Ticket t INDEX BY t.id
			WHERE t.organization = ?1
			ORDER BY t.id DESC
		")->setParameters(array(1=>$org))->setMaxResults($limit)->execute();

		return $tickets;
	}

	public function getRecentOrganizationTickets(Entity\Organization $org, $num = 30)
	{
		$ids = App::getDb()->fetchAllCol("
			SELECT id,
				CASE WHEN `status` =  'awaiting_agent' THEN 1
				WHEN `status` =  'awaiting_user' THEN 2
				WHEN `status` =  'resolved' THEN 3
				WHEN `status` =  'closed' THEN 4
				ELSE 3
				END AS status_order
			FROM tickets
			WHERE organization_id = {$org->id} AND status IN ('awaiting_agent', 'awaiting_user', 'closed', 'resolved')
			ORDER BY status_order ASC, date_created DESC
			LIMIT $num
		", array($org->id));

		if (!$ids) {
			return array();
		}

		$tickets = $this->getEntityManager()->createQuery("
			SELECT t
			FROM DeskPRO:Ticket t INDEX BY t.id
			WHERE t.id IN (?1)
			ORDER BY t.id DESC
		")->setParameters(array(1=>$ids))->setMaxResults($limit)->execute();

		$tickets = \Orb\Util\Arrays::orderIdArray($ids, $tickets);

		return $tickets;
	}


	/**
	 * COunt the total number of tickets that belong to an org
	 *
	 * @param \Application\DeskPRO\Entity\Organization $org
	 * @param null $status
	 * @return int
	 */
	public function countTicketsForOrganization(Entity\Organization $org, $status = null)
	{
		if ($status) {
			$status = (array)$status;
			foreach ($status as &$s) {
				$s = "'$s'";
			}
			$status = implode(',', $status);
		}

		$count = App::getDb()->fetchColumn("
			SELECT COUNT(*)
			FROM tickets
			WHERE organization_id = ? " . ($status ? " AND status IN ($status) " : '') . "
		", array($org['id']));

		return $count;
	}


	/**
	 * Get the latest tickets from a particular user
	 *
	 * @param \Application\DeskPRO\Entity\Person $person
	 * @param int $max The max number of results
	 * @return array
	 */
	public function getLatestByUser(Entity\Person $person, $max = 20)
	{
		$tickets = $this->getEntityManager()->createQuery("
			SELECT t
			FROM DeskPRO:Ticket t
			WHERE t.person = ?1
			ORDER BY t.id DESC
		")->setMaxResults($max)->execute(array(1=> $person));

		return $tickets;
	}


	/**
	 * Executes a query to re-fill the ticket_search_active table
	 */
	public function fillSearchTable()
	{
		App::getDb()->exec("TRUNCATE TABLE tickets_search_active");
		App::getDb()->exec("
			INSERT INTO tickets_search_active (
				`id`, `department_id`, `category_id`,
				`priority_id`, `workflow_id`, `product_id`, `person_id`,
				`agent_id`, `agent_team_id`, `organization_id`, `status`,
				`urgency`, `date_created`, `date_first_agent_reply`, `date_last_agent_reply`,
				`date_last_user_reply`, `date_agent_waiting`, `date_user_waiting`, `total_user_waiting`,
				`total_to_first_reply`
			) SELECT
				`id`, `department_id`, `category_id`,
				`priority_id`, `workflow_id`, `product_id`, `person_id`,
				`agent_id`, `agent_team_id`, `organization_id`, `status`,
				`urgency`, `date_created`, `date_first_agent_reply`, `date_last_agent_reply`,
				`date_last_user_reply`, `date_agent_waiting`, `date_user_waiting`, `total_user_waiting`,
				`total_to_first_reply`
			FROM tickets
			WHERE status IN ('awaiting_agent', 'awaiting_user')
		");
	}

	/**
	 * Checks the database for a duplicate ticket.
	 *
	 * Note: Make sure $ticket has its first message added or else the check
	 * will fail.
	 *
	 * Returns the ticket ID if there was one found, or false if none found.
	 *
	 * @param \Application\DeskPRO\Entity\TicketMessage $message
	 * @param int $secs_ago
	 * @return bool|mixed
	 */
	public function checkDupeTicket($ticket = null, $secs_ago = 10800 /* 3 hours */)
	{
		if (App::getConfig('debug.disable_dupe_check')) {
			return false;
		}

		$timesnip = date_create('-' . $secs_ago . ' seconds');

		$check = $this->getEntityManager()->createQuery("
			SELECT t
			FROM DeskPRO:Ticket t
			WHERE t.ticket_hash = ?1 AND t.date_created > ?2
		")->setParameters(array(1=> $ticket['ticket_hash'], 2=>$timesnip))->getResult();
		if (count($check)) {
			$check = array_shift($check);
		}

		if ($check) {
			return $check;
		}

		return false;
	}


	/**
	 * Count tickets in each of the "archive" statuses:
	 * - hidden.spam
	 * - hidden.awaiting_validation
	 * - resolved
	 * - closed
	 * - hidden.deleted
	 *
	 * @return array
	 */
	public function getArchiveCounts()
	{
		return App::getDb()->fetchAllKeyValue("
			SELECT IF(status = 'hidden', CONCAT('hidden', '.', hidden_status), status) AS status_code, COUNT(*)
			FROM tickets
			WHERE
				status IN ('awaiting_user', 'closed', 'resolved', 'hidden')
			GROUP BY status_code
		");
	}


	public function getTicketIdsWithValidatingEmail($validating_email)
	{
		if (is_object($validating_email)) {
			$validating_email = $validating_email->getId();
		}

		$validating_email = (int)$validating_email;

		return $this->getEntityManager()->getConnection()->fetchAllCol("
			SELECT id
			FROM tickets
			WHERE person_email_validating_id = ?
			ORDER BY id DESC
		", array($validating_email));
	}
}

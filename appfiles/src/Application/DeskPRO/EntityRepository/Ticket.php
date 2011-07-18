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

use \Application\DeskPRO\App;
use \Application\DeskPRO\Entity;
use \Doctrine\ORM\EntityRepository;

use \Orb\Util\Numbers;

class Ticket extends EntityRepository
{
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
	
	public function getTicketsFromIds(array $ids)
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
			WHERE status IN ('open', 'pending')
		");
	}
}
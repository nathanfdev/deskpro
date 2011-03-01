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
	 * Executes a query to re-fill the ticket_search_active table
	 */
	public function fillSearchTable()
	{
		App::getDb()->exec("TRUNCATE TABLE tickets_search_active");
		App::getDb()->exec("
			INSERT INTO tickets_search_active (
				`id`, `language_id`, `department_id`, `category_id`,
				`priority_id`, `workflow_id`, `product_id`, `person_id`,
				`agent_id`, `agent_team_id`, `organization_id`, `status`,
				`urgency`, `date_created`, `date_first_agent_reply`, `date_last_agent_reply`,
				`date_last_user_reply`, `date_agent_waiting`, `date_user_waiting`, `total_user_waiting`,
				`total_to_first_reply`
			) SELECT
				`id`, `language_id`, `department_id`, `category_id`,
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
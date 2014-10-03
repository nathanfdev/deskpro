<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/  |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2014, DeskPRO Ltd.             |
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
 * @category Entities
 * @copyright Copyright (c) 2011 DeskPRO (http://www.deskpro.com/)
 */

namespace Application\DeskPRO\EntityRepository;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity\Person as PersonEntity;
use Application\DeskPRO\Entity\Ticket as TicketEntity;
use Application\DeskPRO\Entity;
use Orb\Util\Dates;

class Task extends AbstractEntityRepository
{
	/**
	 * Count pending tasks.
	 *
	 * @return int
	 */
	public function countPendingTasks(Entity\Person $person)
	{
		return $this->filterAllPendingTasks($person, '#total', null, null, 'incomplete');
	}

	/**
	 * Count overdue tasks.
	 *
	 * @param string $time_zone The time zone
	 * @return int
	 */
	public function countOverdueTasks(Entity\Person $person)
	{
		return $this->filterAllPendingTasks($person, '#overdue', null, null, 'incomplete');
	}

	/**
	 * Count due today tasks.
	 *
	 * @param string $time_zone The time zone
	 * @return int
	 */
	public function countDueTodayTasks(Entity\Person $person)
	{
		return $this->filterAllPendingTasks($person, '#today', null, null, 'incomplete');
	}

    /**
	 * Count due in future tasks.
	 *
	 * @param string $time_zone The time zone
	 * @return int
	 */
	public function countDueFutureTasks(Entity\Person $person)
	{
		return $this->filterAllPendingTasks($person, '#future', null, null, 'incomplete');
	}

	/**
	 * Count pending tasks assigned to the person.
	 *
	 * @param Person $person The person
	 * @return int
	 */
	public function countPendingTasksForPerson(Entity\Person $person)
	{
		return $this->filterTasksForPerson($person, '#total', null, null, 'incomplete');
	}

	/**
	 * Count overdue tasks assigned to the person.
	 *
	 * @param Person $person The person
	 * @return int
	 */
        public function countOverdueTasksForPerson(Entity\Person $person)
        {
			return $this->filterTasksForPerson($person, '#overdue', null, null, 'incomplete');
    }

	/**
	 * Count due today tasks assigned to the person.
	 *
	 * @param Person $person The person
	 * @return int
	 */
	public function countDueTodayTasksForPerson(Entity\Person $person)
	{
		return $this->filterTasksForPerson($person, '#today', null, null, 'incomplete');
	}


        /**
	 * Count due in future tasks assigned to the person.
	 *
	 * @param Person $person The person
	 * @return int
	 */
	public function countDueFutureTasksForPerson(Entity\Person $person)
	{
		return $this->filterTasksForPerson($person, '#future', null, null, 'incomplete');
	}

	/**
	 * Count all pending tasks assigned to the person's teams.
	 *
	 * @param Entity\Person $person The person
	 * @return int
	 */
	public function countPendingTaksForPersonTeams(Entity\Person $person)
	{
		return $this->filterTaksForPersonTeams($person, '#total', null, null, 'incomplete');
	}

	/**
	 * Count overdue tasks assigned to the perso's teams.
	 *
	 * @param Person $person The person
	 * @return int
	 */
	public function countOverdueTasksForPersonTeams(Entity\Person $person)
	{
		return $this->filterTaksForPersonTeams($person, '#overdue', null, null, 'incomplete');
	}

	/**
	 * Count due today tasks assigned to the person's teamsT.
	 *
	 * @param Person $person The person
	 * @return int
	 */
	public function countDueTodayTasksForPersonTeams(Entity\Person $person)
	{
		return $this->filterTaksForPersonTeams($person, '#today', null, null, 'incomplete');
	}

        /**
	 * Count due in future tasks assigned to the person's teams.
	 *
	 * @param Person $person The person
	 * @return int
	 */
	public function countDueFutureTasksForPersonTeams(Entity\Person $person)
	{
		return $this->filterTaksForPersonTeams($person, '#future', null, null, 'incomplete');
	}


	/**
	 * Count pending delegated tasks assigned to the person.
	 *
	 * @param Person $person The person
	 * @return int
	 */
	public function countPendingDelegatedTasksForPerson(Entity\Person $person)
	{
		return $this->filterDelegatedTasksForPerson($person, '#total', null, null, 'incomplete');
	}

	/**
	 * Count overdue delegated tasks assigned to the person.
	 *
	 * @param Person $person The person
	 * @return int
	 */
	public function countOverdueDelegatedTasksForPerson(Entity\Person $person)
	{
		return $this->filterDelegatedTasksForPerson($person, '#overdue', null, null, 'incomplete');
	}

	/**
	 * Count due today delegated tasks assigned to the person.
	 *
	 * @param Person $person The person
	 * @return int
	 */
	public function countDueTodayDelegatedTasksForPerson(Entity\Person $person)
	{
		return $this->filterDelegatedTasksForPerson($person, '#today', null, null, 'incomplete');
	}

        /**
	 * Count due in future delegated tasks assigned to the person.
	 *
	 * @param Person $person The person
	 * @return int
	 */
	public function countDueFutureDelegatedTasksForPerson(Entity\Person $person)
	{
		return $this->filterDelegatedTasksForPerson($person, '#future', null, null, 'incomplete');
	}

    /**
	 * All pending tasks assigned to the person.
	 *
	 * @param Person $person The person
         * @param string $filter_type
	 * @return task object
	 */
	public function filterTasksForPerson(Entity\Person $person, $filter_type = 'total', $limit = null, $offset = null, $state = null)
	{
		$today = $person->getDateTime();
		$today->setTime(0,0,0);
		$today = Dates::convertToUtcDateTime($today);

		$tomorrow = $person->getDateTime();
		$tomorrow->setTime(23, 59, 59);
		$tomorrow = Dates::convertToUtcDateTime($tomorrow);

		$now = new \DateTime();

		$params = array();

		$is_count = false;
		if ($filter_type[0] == '#') {
			$is_count = true;
			$filter_type = substr($filter_type, 1);
		}

		if($filter_type == 'today') {
			$where_part = '((date_due >= ? AND date_due <= ?) OR date_due IS NULL)';
			$params[] = $today->format('Y-m-d H:i:s');
			$params[] = $tomorrow->format('Y-m-d H:i:s');
		} elseif($filter_type == 'future') {
			$where_part = '(date_due >= ?)';
			$params[] = $tomorrow->format('Y-m-d H:i:s');
		} elseif($filter_type == 'overdue') {
			$where_part = '(date_due < ?)';
			$params[] = $now->format('Y-m-d H:i:s');
		} else {
			$where_part = '1';
		}

		$person->loadHelper('Agent');
		if ($team_ids = $person->Agent->getTeamIds()) {
			$team_ids = implode(',', $team_ids);
			$where_part .= ' AND ( (assigned_agent_id = ? OR assigned_agent_team_id IN ('.$team_ids.')) OR (assigned_agent_id IS NULL AND assigned_agent_team_id IS NULL AND person_id = ?) )';
			$params[] = $person->id;
			$params[] = $person->id;
		} else {
			$where_part .= ' AND ( (assigned_agent_id = ?) OR (assigned_agent_id IS NULL AND assigned_agent_team_id IS NULL AND person_id = ?) )';
			$params[] = $person->id;
			$params[] = $person->id;
		}

		if ($state !== null) {
			if ($state == 'complete') {
				$where_part .= ' AND is_completed = 1';
			} elseif ($state == 'incomplete') {
				$where_part .= ' AND is_completed = 0';
			}
		}

		if ($limit) {
			if ($offset) {
				$limit_part = "LIMIT $offset, $limit";
			} else {
				$limit_part = "LIMIT 0, $limit";
			}
		}

		if (!$is_count) {
			$result_ids = App::getDb()->fetchAllCol("
				SELECT id, COALESCE(date_due, NOW()) AS sort_date_due
				FROM tasks
				WHERE $where_part
				ORDER BY sort_date_due ASC, id DESC
				$limit_part
			", $params);

			$results = array();
			if ($result_ids) {
				$results = $this->getByIds($result_ids, true);
			}

			return $results;
		} else {
			return App::getDb()->fetchColumn("
				SELECT COUNT(*)
				FROM tasks
				WHERE $where_part
			", $params);
		}
	}

        /**
	 * All pending tasks assigned to the person's teams.
	 *
	 * @param Entity\Person $person The person
         * @param string $filter_type
	 * @return Task Object
	 */
	public function filterTaksForPersonTeams(Entity\Person $person, $filter_type = 'total', $limit = null, $offset = null, $state = null)
	{
		$today = $person->getDateTime();
		$today->setTime(0,0,0);
		$today = Dates::convertToUtcDateTime($today);

		$tomorrow = $person->getDateTime();
		$tomorrow->setTime(23, 59, 59);
		$tomorrow = Dates::convertToUtcDateTime($tomorrow);

		$now = new \DateTime();

		$params = array();

		$is_count = false;
		if ($filter_type[0] == '#') {
			$is_count = true;
			$filter_type = substr($filter_type, 1);
		}

		if($filter_type == 'today') {
			$where_part = '((date_due >= ? AND date_due <= ?) OR date_due IS NULL)';
			$params[] = $today->format('Y-m-d H:i:s');
			$params[] = $tomorrow->format('Y-m-d H:i:s');
		} elseif($filter_type == 'future') {
			$where_part = '(date_due >= ?)';
			$params[] = $tomorrow->format('Y-m-d H:i:s');
		} elseif($filter_type == 'overdue') {
			$where_part = '(date_due < ?)';
			$params[] = $now->format('Y-m-d H:i:s');
		} else {
			$where_part = '1';
		}

		$person->loadHelper('Agent');
		if ($team_ids = $person->Agent->getTeamIds()) {
			$team_ids = implode(',', $team_ids);
			$where_part .= ' AND (assigned_agent_team_id IN ('.$team_ids.'))';
		} else {
			// Doesnt belong to any teams, so nothing to show
			if (!$is_count) {
				return array();
			} else {
				return 0;
			}
		}

		if ($state !== null) {
			if ($state == 'complete') {
				$where_part .= ' AND is_completed = 1';
			} elseif ($state == 'incomplete') {
				$where_part .= ' AND is_completed = 0';
			}
		}

		if ($limit) {
			if ($offset) {
				$limit_part = "LIMIT $offset, $limit";
			} else {
				$limit_part = "LIMIT 0, $limit";
			}
		}

		if (!$is_count) {
			$result_ids = App::getDb()->fetchAllCol("
				SELECT id, COALESCE(date_due, NOW()) AS sort_date_due
				FROM tasks
				WHERE $where_part
				ORDER BY sort_date_due ASC, id DESC
				$limit_part
			", $params);

			$results = array();
			if ($result_ids) {
				$results = $this->getByIds($result_ids, true);
			}

			return $results;
		} else {
			return App::getDb()->fetchColumn("
				SELECT COUNT(*)
				FROM tasks
				WHERE $where_part
			", $params);
		}
	}

    /**
	 * Count pending delegated tasks assigned to the person.
	 *
	 * @param Person $person The person
	 * @return int
	 */
	public function filterDelegatedTasksForPerson(Entity\Person $person, $filter_type = 'total', $limit = null, $offset = null, $state = null)
	{
		$today = $person->getDateTime();
		$today->setTime(0,0,0);
		$today = Dates::convertToUtcDateTime($today);

		$tomorrow = $person->getDateTime();
		$tomorrow->setTime(23, 59, 59);
		$tomorrow = Dates::convertToUtcDateTime($tomorrow);

		$now = new \DateTime();

		$params = array();

		$is_count = false;
		if ($filter_type[0] == '#') {
			$is_count = true;
			$filter_type = substr($filter_type, 1);
		}

		if($filter_type == 'today') {
			$where_part = '((date_due >= ? AND date_due <= ?) OR date_due IS NULL)';
			$params[] = $today->format('Y-m-d H:i:s');
			$params[] = $tomorrow->format('Y-m-d H:i:s');
		} elseif($filter_type == 'future') {
			$where_part = '(date_due >= ?)';
			$params[] = $tomorrow->format('Y-m-d H:i:s');
		} elseif($filter_type == 'overdue') {
			$where_part = '(date_due < ?)';
			$params[] = $now->format('Y-m-d H:i:s');
		} else {
			$where_part = '1';
		}

		$where_part .= " AND person_id = ? ";
		$params[] = $person->id;

		$person->loadHelper('Agent');
		if ($team_ids = $person->Agent->getTeamIds()) {
			$team_ids = implode(',', $team_ids);
			$where_part .= ' AND ( (assigned_agent_id IS NOT NULL AND assigned_agent_id != ?) OR (assigned_agent_team_id IS NOT NULL AND assigned_agent_team_id NOT IN ('.$team_ids.')) )';
			$params[] = $person->id;
		} else {
			$where_part .= ' AND ( (assigned_agent_id IS NOT NULL AND assigned_agent_id != ?) OR (assigned_agent_team_id IS NOT NULL) )';
			$params[] = $person->id;
		}

		if ($state !== null) {
			if ($state == 'complete') {
				$where_part .= ' AND is_completed = 1';
			} elseif ($state == 'incomplete') {
				$where_part .= ' AND is_completed = 0';
			}
		}

		if (!$is_count) {
			if ($limit) {
				if ($offset) {
					$limit_part = "LIMIT $offset, $limit";
				} else {
					$limit_part = "LIMIT 0, $limit";
				}
			}

			$result_ids = App::getDb()->fetchAllCol("
				SELECT id, COALESCE(date_due, NOW()) AS sort_date_due
				FROM tasks
				WHERE $where_part
				ORDER BY sort_date_due ASC, id DESC
				$limit_part
			", $params);

			$results = array();
			if ($result_ids) {
				$results = $this->getByIds($result_ids, true);
			}

			return $results;
		} else {
			return App::getDb()->fetchColumn("
				SELECT COUNT(*)
				FROM tasks
				WHERE $where_part
			", $params);
		}
	}

        /**
	 * Filter all pending tasks.
	 *
         * @param string $filter_type
	 * @return int
	 */
	public function filterAllPendingTasks(Entity\Person $person, $filter_type = 'total', $limit = null, $offset = null, $state = null)
	{
		$today = $person->getDateTime();
		$today->setTime(0,0,0);
		$today = Dates::convertToUtcDateTime($today);

		$tomorrow = $person->getDateTime();
		$tomorrow->setTime(23, 59, 59);
		$tomorrow = Dates::convertToUtcDateTime($tomorrow);

		$now = new \DateTime();

		$is_count = false;
		if ($filter_type[0] == '#') {
			$is_count = true;
			$filter_type = substr($filter_type, 1);
		}

		$params = array();

		if($filter_type == 'today') {
			$where_part = '((date_due >= ? AND date_due <= ?) OR date_due IS NULL)';
			$params[] = $today->format('Y-m-d H:i:s');
			$params[] = $tomorrow->format('Y-m-d H:i:s');
		} elseif($filter_type == 'future') {
			$where_part = '(date_due >= ?)';
			$params[] = $tomorrow->format('Y-m-d H:i:s');
		} elseif($filter_type == 'overdue') {
			$where_part = '(date_due < ?)';
			$params[] = $now->format('Y-m-d H:i:s');
		} else {
			$where_part = '1';
		}

		$person->loadHelper('Agent');
		if ($team_ids = $person->Agent->getTeamIds()) {
			$team_ids = implode(',', $team_ids);
			$where_part .= ' AND ( (person_id = ? OR assigned_agent_id = ? OR assigned_agent_team_id IN ('.$team_ids.')) OR visibility = 1)';
			$params[] = $person->id;
			$params[] = $person->id;
		} else {
			$where_part .= ' AND ( (person_id = ? OR assigned_agent_id = ?) OR visibility = 1)';
			$params[] = $person->id;
			$params[] = $person->id;
		}

		if ($state !== null) {
			if ($state == 'complete') {
				$where_part .= ' AND is_completed = 1';
			} elseif ($state == 'incomplete') {
				$where_part .= ' AND is_completed = 0';
			}
		}

		if ($limit) {
			if ($offset) {
				$limit_part = "LIMIT $offset, $limit";
			} else {
				$limit_part = "LIMIT 0, $limit";
			}
		}

		if (!$is_count) {
			$result_ids = App::getDb()->fetchAllCol("
				SELECT id, COALESCE(date_due, NOW()) AS sort_date_due
				FROM tasks
				WHERE $where_part
				ORDER BY sort_date_due ASC, id DESC
				$limit_part
			", $params);

			$results = array();
			if ($result_ids) {
				$results = $this->getByIds($result_ids, true);
			}

			return $results;
		} else {
			return App::getDb()->fetchColumn("
				SELECT COUNT(*)
				FROM tasks
				WHERE $where_part
			", $params);
		}
	}

	public function findLinkedTicketTasks(TicketEntity $ticket, PersonEntity $person_context, $all = false)
	{
		$person_context->loadHelper('Agent');
		if ($person_context->Agent->getTeamIds()) {
			$team_ids = $person_context->Agent->getTeamIds();
		} else {
			$team_ids = array(0);
		}

		$team_ids = implode(',', $team_ids);

		if ($all) {
			$task_ids = App::getDb()->fetchAllCol("
				SELECT tasks.id
				FROM tasks
				LEFT JOIN task_associations ON task_associations.task_id = tasks.id
				WHERE
					((tasks.person_id = ? OR tasks.assigned_agent_id = ? OR tasks.assigned_agent_team_id IN ($team_ids)) OR tasks.visibility = 1)
					AND task_associations.ticket_id = ?
					ORDER BY tasks.date_due ASC
			", array(
				$person_context->getId(),
				$person_context->getId(),
				$ticket->getId()
			));
		} else {
			$task_ids = App::getDb()->fetchAllCol("
				SELECT tasks.id
				FROM tasks
				LEFT JOIN task_associations ON task_associations.task_id = tasks.id
				WHERE
					tasks.is_completed = 0
					AND ((tasks.person_id = ? OR tasks.assigned_agent_id = ? OR tasks.assigned_agent_team_id IN ($team_ids)) OR tasks.visibility = 1)
					AND task_associations.ticket_id = ?
					ORDER BY tasks.date_due ASC
			", array(
				$person_context->getId(),
				$person_context->getId(),
				$ticket->getId()
			));
		}

		if (!$task_ids) {
			return array();
		}

		return $this->getByIds($task_ids, true);
	}
}

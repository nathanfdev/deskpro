<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2017, DeskPRO Ltd.
 *
 * The license agreement under which this software is released
 * can be found at https://www.deskpro.com/eula/
 *
 * By using this software, you acknowledge having read the license
 * and agree to be bound thereby.
 *
 * Please note that DeskPRO is not free software. We release the full
 * source code for our software because we trust our users to pay us for
 * the huge investment in time and energy that has gone into both creating
 * this software and supporting our customers. By providing the source code
 * we preserve our customers' ability to modify, audit and learn from our
 * work. We have been developing DeskPRO since 2001, please help us make it
 * another decade.
 *
 * Like the work you see? Think you could make it better? We are always
 * looking for great developers to join us: http://www.deskpro.com/jobs/
 *
 * ~ Thanks, Everyone at Team DeskPRO
 */

/**
 * DeskPRO.
 *
 * @category Entities
 *
 * @copyright Copyright (c) 2011 DeskPRO (http://www.deskpro.com/)
 */

namespace Application\DeskPRO\EntityRepository;

use Application\DeskPRO\App;
use Application\DeskPRO\DBAL\Connection;
use Application\DeskPRO\Entity;
use Application\DeskPRO\Entity\Person as PersonEntity;
use Application\DeskPRO\Entity\Ticket as TicketEntity;
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
     *
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
     *
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
     *
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
     *
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
     *
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
     *
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
     *
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
     *
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
     *
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
     *
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
     *
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
     *
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
     *
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
     *
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
     *
     * @return int
     */
    public function countDueFutureDelegatedTasksForPerson(Entity\Person $person)
    {
        return $this->filterDelegatedTasksForPerson($person, '#future', null, null, 'incomplete');
    }

    /**
     * All pending tasks assigned to the person.
     *
     * @param PersonEntity $person     The person
     * @param string       $filterType
     * @param null         $limit
     * @param null         $offset
     * @param null         $state
     *
     * @return Task object
     */
    public function filterTasksForPerson(PersonEntity $person, $filterType = 'total', $limit = null, $offset = null, $state = null)
    {
        $today = $person->getDateTime();
        $today->setTime(0, 0, 0);
        $today = Dates::convertToUtcDateTime($today);

        $tomorrow = $person->getDateTime();
        $tomorrow->modify('+1 day')->setTime(0, 0, 0);
        $tomorrow = Dates::convertToUtcDateTime($tomorrow);

        $now = new \DateTime();

        $params = [];

        $isCount = false;
        if ($filterType[0] == '#') {
            $isCount    = true;
            $filterType = substr($filterType, 1);
        }

        if ($filterType == 'today') {
            $wherePart = '((date_due >= ? AND date_due <= ?) OR date_due IS NULL)';
            $params[]  = $today->format('Y-m-d H:i:s');
            $params[]  = $tomorrow->format('Y-m-d H:i:s');
        } elseif ($filterType == 'future') {
            $wherePart = '(date_due >= ?)';
            $params[]  = $tomorrow->format('Y-m-d H:i:s');
        } elseif ($filterType == 'overdue') {
            $wherePart = '(date_due < ?)';
            $params[]  = $now->format('Y-m-d H:i:s');
        } else {
            $wherePart = '1';
        }

        $person->loadHelper('Agent');
        if ($teamIds = $person->Agent->getTeamIds()) {
            $teamIds = implode(',', $teamIds);
            $wherePart .= ' AND ( (assigned_agent_id = ? OR assigned_agent_team_id IN ('.$teamIds.')) OR (assigned_agent_id IS NULL AND assigned_agent_team_id IS NULL AND person_id = ?) )';
            $params[] = $person->id;
            $params[] = $person->id;
        } else {
            $wherePart .= ' AND ( (assigned_agent_id = ?) OR (assigned_agent_id IS NULL AND assigned_agent_team_id IS NULL AND person_id = ?) )';
            $params[] = $person->id;
            $params[] = $person->id;
        }

        if ($state !== null) {
            if ($state == 'complete') {
                $wherePart .= ' AND is_completed = 1';
            } elseif ($state == 'incomplete') {
                $wherePart .= ' AND is_completed = 0';
            }
        }

        $limitPart = '';

        if ($limit) {
            if ($offset) {
                $limitPart = "LIMIT $offset, $limit";
            } else {
                $limitPart = "LIMIT 0, $limit";
            }
        }

        if (!$isCount) {
            $resultIds = $this->getEntityManager()->getConnection()->fetchAllCol("
                SELECT id, COALESCE(date_due, NOW()) AS sort_date_due
                FROM tasks
                WHERE $wherePart
                ORDER BY sort_date_due ASC, id DESC
                $limitPart
            ", $params);

            $results = [];
            if ($resultIds) {
                $results = $this->getByIds($resultIds, true);
            }

            return $results;
        } else {
            return $this->getEntityManager()->getConnection()->fetchColumn("
                SELECT COUNT(*)
                FROM tasks
                WHERE $wherePart
            ", $params);
        }
    }

    /**
     * All pending tasks assigned to the person's teams.
     *
     * @param Entity\Person $person     The person
     * @param string        $filterType
     *
     * @return Task Object
     */
    public function filterTaksForPersonTeams(Entity\Person $person, $filterType = 'total', $limit = null, $offset = null, $state = null)
    {
        $today = $person->getDateTime();
        $today->setTime(0, 0, 0);
        $today = Dates::convertToUtcDateTime($today);

        $tomorrow = $person->getDateTime();
        $tomorrow->modify('+1 day')->setTime(0, 0, 0);
        $tomorrow = Dates::convertToUtcDateTime($tomorrow);

        $now = new \DateTime();

        $params = [];

        $isCount = false;
        if ($filterType[0] == '#') {
            $isCount    = true;
            $filterType = substr($filterType, 1);
        }

        if ($filterType == 'today') {
            $wherePart = '((date_due >= ? AND date_due <= ?) OR date_due IS NULL)';
            $params[]  = $today->format('Y-m-d H:i:s');
            $params[]  = $tomorrow->format('Y-m-d H:i:s');
        } elseif ($filterType == 'future') {
            $wherePart = '(date_due >= ?)';
            $params[]  = $tomorrow->format('Y-m-d H:i:s');
        } elseif ($filterType == 'overdue') {
            $wherePart = '(date_due < ?)';
            $params[]  = $now->format('Y-m-d H:i:s');
        } else {
            $wherePart = '1';
        }

        $person->loadHelper('Agent');
        if ($teamIds = $person->Agent->getTeamIds()) {
            $teamIds = implode(',', $teamIds);
            $wherePart .= ' AND (assigned_agent_team_id IN ('.$teamIds.'))';
        } else {
            // Doesnt belong to any teams, so nothing to show
            if (!$isCount) {
                return [];
            } else {
                return 0;
            }
        }

        if ($state !== null) {
            if ($state == 'complete') {
                $wherePart .= ' AND is_completed = 1';
            } elseif ($state == 'incomplete') {
                $wherePart .= ' AND is_completed = 0';
            }
        }

        $limitPart = '';

        if ($limit) {
            if ($offset) {
                $limitPart = "LIMIT $offset, $limit";
            } else {
                $limitPart = "LIMIT 0, $limit";
            }
        }

        if (!$isCount) {
            $resultIds = $this->getEntityManager()->getConnection()->fetchAllCol("
                SELECT id, COALESCE(date_due, NOW()) AS sort_date_due
                FROM tasks
                WHERE $wherePart
                ORDER BY sort_date_due ASC, id DESC
                $limitPart
            ", $params);

            $results = [];
            if ($resultIds) {
                $results = $this->getByIds($resultIds, true);
            }

            return $results;
        } else {
            return $this->getEntityManager()->getConnection()->fetchColumn("
                SELECT COUNT(*)
                FROM tasks
                WHERE $wherePart
            ", $params);
        }
    }

    /**
     * Count pending delegated tasks assigned to the person.
     *
     * @param Person $person The person
     *
     * @return int
     */
    public function filterDelegatedTasksForPerson(Entity\Person $person, $filterType = 'total', $limit = null, $offset = null, $state = null)
    {
        $today = $person->getDateTime();
        $today->setTime(0, 0, 0);
        $today = Dates::convertToUtcDateTime($today);

        $tomorrow = $person->getDateTime();
        $tomorrow->modify('+1 day')->setTime(0, 0, 0);
        $tomorrow = Dates::convertToUtcDateTime($tomorrow);

        $now = new \DateTime();

        $params = [];

        $isCount = false;
        if ($filterType[0] == '#') {
            $isCount    = true;
            $filterType = substr($filterType, 1);
        }

        if ($filterType == 'today') {
            $wherePart = '((date_due >= ? AND date_due <= ?) OR date_due IS NULL)';
            $params[]  = $today->format('Y-m-d H:i:s');
            $params[]  = $tomorrow->format('Y-m-d H:i:s');
        } elseif ($filterType == 'future') {
            $wherePart = '(date_due >= ?)';
            $params[]  = $tomorrow->format('Y-m-d H:i:s');
        } elseif ($filterType == 'overdue') {
            $wherePart = '(date_due < ?)';
            $params[]  = $now->format('Y-m-d H:i:s');
        } else {
            $wherePart = '1';
        }

        $wherePart .= ' AND person_id = ? ';
        $params[] = $person->id;

        $person->loadHelper('Agent');
        if ($teamIds = $person->Agent->getTeamIds()) {
            $teamIds = implode(',', $teamIds);
            $wherePart .= ' AND ( (assigned_agent_id IS NOT NULL AND assigned_agent_id != ?) OR (assigned_agent_team_id IS NOT NULL AND assigned_agent_team_id NOT IN ('.$teamIds.')) )';
            $params[] = $person->id;
        } else {
            $wherePart .= ' AND ( (assigned_agent_id IS NOT NULL AND assigned_agent_id != ?) OR (assigned_agent_team_id IS NOT NULL) )';
            $params[] = $person->id;
        }

        if ($state !== null) {
            if ($state == 'complete') {
                $wherePart .= ' AND is_completed = 1';
            } elseif ($state == 'incomplete') {
                $wherePart .= ' AND is_completed = 0';
            }
        }

        $limitPart = '';

        if (!$isCount) {
            if ($limit) {
                if ($offset) {
                    $limitPart = "LIMIT $offset, $limit";
                } else {
                    $limitPart = "LIMIT 0, $limit";
                }
            }

            $resultIds = $this->getEntityManager()->getConnection()->fetchAllCol("
                SELECT id, COALESCE(date_due, NOW()) AS sort_date_due
                FROM tasks
                WHERE $wherePart
                ORDER BY sort_date_due ASC, id DESC
                $limitPart
            ", $params);

            $results = [];
            if ($resultIds) {
                $results = $this->getByIds($resultIds, true);
            }

            return $results;
        } else {
            return App::getDb()->fetchColumn("
                SELECT COUNT(*)
                FROM tasks
                WHERE $wherePart
            ", $params);
        }
    }

    /**
     * Filter all pending tasks.
     *
     * @param string $filterType
     *
     * @return int
     */
    public function filterAllPendingTasks(Entity\Person $person, $filterType = 'total', $limit = null, $offset = null, $state = null)
    {
        $today = $person->getDateTime();
        $today->setTime(0, 0, 0);
        $today = Dates::convertToUtcDateTime($today);

        $tomorrow = $person->getDateTime();
        $tomorrow->modify('+1 day')->setTime(0, 0, 0);
        $tomorrow = Dates::convertToUtcDateTime($tomorrow);

        $now = new \DateTime();

        $isCount = false;
        if ($filterType[0] == '#') {
            $isCount    = true;
            $filterType = substr($filterType, 1);
        }

        $params = [];

        if ($filterType == 'today') {
            $wherePart = '((date_due >= ? AND date_due <= ?) OR date_due IS NULL)';
            $params[]  = $today->format('Y-m-d H:i:s');
            $params[]  = $tomorrow->format('Y-m-d H:i:s');
        } elseif ($filterType == 'future') {
            $wherePart = '(date_due >= ?)';
            $params[]  = $tomorrow->format('Y-m-d H:i:s');
        } elseif ($filterType == 'overdue') {
            $wherePart = '(date_due < ?)';
            $params[]  = $now->format('Y-m-d H:i:s');
        } else {
            $wherePart = '1';
        }

        $person->loadHelper('Agent');
        if ($teamIds = $person->Agent->getTeamIds()) {
            $teamIds = implode(',', $teamIds);
            $wherePart .= ' AND ( (person_id = ? OR assigned_agent_id = ? OR assigned_agent_team_id IN ('.$teamIds.')) OR visibility = 1)';
            $params[] = $person->id;
            $params[] = $person->id;
        } else {
            $wherePart .= ' AND ( (person_id = ? OR assigned_agent_id = ?) OR visibility = 1)';
            $params[] = $person->id;
            $params[] = $person->id;
        }

        if ($state !== null) {
            if ($state == 'complete') {
                $wherePart .= ' AND is_completed = 1';
            } elseif ($state == 'incomplete') {
                $wherePart .= ' AND is_completed = 0';
            }
        }

        $limitPart = '';

        if ($limit) {
            if ($offset) {
                $limitPart = "LIMIT $offset, $limit";
            } else {
                $limitPart = "LIMIT 0, $limit";
            }
        }

        if (!$isCount) {
            $resultIds = $this->getEntityManager()->getConnection()->fetchAllCol("
                SELECT id, COALESCE(date_due, NOW()) AS sort_date_due
                FROM tasks
                WHERE $wherePart
                ORDER BY sort_date_due ASC, id DESC
                $limitPart
            ", $params);

            $results = [];
            if ($resultIds) {
                $results = $this->getByIds($resultIds, true);
            }

            return $results;
        } else {
            return App::getDb()->fetchColumn("
                SELECT COUNT(*)
                FROM tasks
                WHERE $wherePart
            ", $params);
        }
    }

    /**
     * @param TicketEntity $ticket
     * @param PersonEntity $personContext
     * @param bool         $all
     *
     * @return array
     */
    public function findLinkedTicketTasks(TicketEntity $ticket, PersonEntity $personContext, $all = false)
    {
        /** @var Connection $connection */
        $connection = $this->_em->getConnection();

        $teamIds = $connection->fetchAllCol('SELECT team_id FROM agent_team_members WHERE person_id = ?', [$personContext->getId()]);
        if (!$teamIds) {
            $teamIds = [0];
        }

        if ($all) {
            $taskIds = $connection->fetchAllCol('
                SELECT tasks.id
                FROM tasks
                LEFT JOIN task_associations ON task_associations.task_id = tasks.id
                WHERE
                    ((tasks.person_id = ? OR tasks.assigned_agent_id = ? OR tasks.assigned_agent_team_id IN (?)) OR tasks.visibility = 1)
                    AND task_associations.ticket_id = ?
                    ORDER BY tasks.date_due ASC
            ', [
                $personContext->getId(),
                $personContext->getId(),
                $teamIds,
                $ticket->getId(),
            ], [
                \PDO::PARAM_INT,
                \PDO::PARAM_INT,
                Connection::PARAM_INT_ARRAY,
                \PDO::PARAM_INT,
            ]);
        } else {
            $taskIds = $connection->fetchAllCol('
                SELECT tasks.id
                FROM tasks
                LEFT JOIN task_associations ON task_associations.task_id = tasks.id
                WHERE
                    tasks.is_completed = 0
                    AND ((tasks.person_id = ? OR tasks.assigned_agent_id = ? OR tasks.assigned_agent_team_id IN (?)) OR tasks.visibility = 1)
                    AND task_associations.ticket_id = ?
                    ORDER BY tasks.date_due ASC
            ', [
                $personContext->getId(),
                $personContext->getId(),
                $teamIds,
                $ticket->getId(),
            ], [
                \PDO::PARAM_INT,
                \PDO::PARAM_INT,
                Connection::PARAM_INT_ARRAY,
                \PDO::PARAM_INT,
            ]);
        }

        if (!$taskIds) {
            return [];
        }

        return $this->getByIds($taskIds, true);
    }
}

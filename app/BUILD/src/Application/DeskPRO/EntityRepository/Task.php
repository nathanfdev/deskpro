<?php

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
use Application\DeskPRO\People\Helpers\AgentPermissions;
use Doctrine\ORM\Query\Expr\Join;
use Orb\Util\Dates;

class Task extends AbstractEntityRepository
{
    /**
     * Count pending tasks.
     *
     * @param PersonEntity $person The person
     *
     * @return int
     */
    public function countPendingTasks(PersonEntity $person)
    {
        return $this->filterAllPendingTasks($person, '#total', null, null, 'incomplete');
    }

    /**
     * Count overdue tasks.
     *
     * @param PersonEntity $person The person
     *
     * @return int
     */
    public function countOverdueTasks(PersonEntity $person)
    {
        return $this->filterAllPendingTasks($person, '#overdue', null, null, 'incomplete');
    }

    /**
     * Count due today tasks.
     *
     * @param PersonEntity $person The person
     *
     * @return int
     */
    public function countDueTodayTasks(PersonEntity $person)
    {
        return $this->filterAllPendingTasks($person, '#today', null, null, 'incomplete');
    }

    /**
     * Count due in future tasks.
     *
     * @param PersonEntity $person The person
     *
     * @return int
     */
    public function countDueFutureTasks(PersonEntity $person)
    {
        return $this->filterAllPendingTasks($person, '#future', null, null, 'incomplete');
    }

    /**
     * Count pending tasks assigned to the person.
     *
     * @param PersonEntity $person The person
     *
     * @return int
     */
    public function countPendingTasksForPerson(PersonEntity $person)
    {
        return $this->filterTasksForPerson($person, '#total', null, null, 'incomplete');
    }

    /**
     * Count overdue tasks assigned to the person.
     *
     * @param PersonEntity $person The person
     *
     * @return int
     */
    public function countOverdueTasksForPerson(PersonEntity $person)
    {
        return $this->filterTasksForPerson($person, '#overdue', null, null, 'incomplete');
    }

    /**
     * Count due today tasks assigned to the person.
     *
     * @param PersonEntity $person The person
     *
     * @return int
     */
    public function countDueTodayTasksForPerson(PersonEntity $person)
    {
        return $this->filterTasksForPerson($person, '#today', null, null, 'incomplete');
    }

    /**
     * Count due in future tasks assigned to the person.
     *
     * @param PersonEntity $person The person
     *
     * @return int
     */
    public function countDueFutureTasksForPerson(PersonEntity $person)
    {
        return $this->filterTasksForPerson($person, '#future', null, null, 'incomplete');
    }

    /**
     * Count all pending tasks assigned to the person's teams.
     *
     * @param PersonEntity $person The person
     *
     * @return int
     */
    public function countPendingTaksForPersonTeams(PersonEntity $person)
    {
        return $this->filterTaksForPersonTeams($person, '#total', null, null, 'incomplete');
    }

    /**
     * Count overdue tasks assigned to the perso's teams.
     *
     * @param PersonEntity $person The person
     *
     * @return int
     */
    public function countOverdueTasksForPersonTeams(PersonEntity $person)
    {
        return $this->filterTaksForPersonTeams($person, '#overdue', null, null, 'incomplete');
    }

    /**
     * Count due today tasks assigned to the person's teamsT.
     *
     * @param PersonEntity $person The person
     *
     * @return int
     */
    public function countDueTodayTasksForPersonTeams(PersonEntity $person)
    {
        return $this->filterTaksForPersonTeams($person, '#today', null, null, 'incomplete');
    }

    /**
     * Count due in future tasks assigned to the person's teams.
     *
     * @param PersonEntity $person The person
     *
     * @return int
     */
    public function countDueFutureTasksForPersonTeams(PersonEntity $person)
    {
        return $this->filterTaksForPersonTeams($person, '#future', null, null, 'incomplete');
    }

    /**
     * Count pending delegated tasks assigned to the person.
     *
     * @param PersonEntity $person The person
     *
     * @return int
     */
    public function countPendingDelegatedTasksForPerson(PersonEntity $person)
    {
        return $this->filterDelegatedTasksForPerson($person, '#total', null, null, 'incomplete');
    }

    /**
     * Count overdue delegated tasks assigned to the person.
     *
     * @param PersonEntity $person The person
     *
     * @return int
     */
    public function countOverdueDelegatedTasksForPerson(PersonEntity $person)
    {
        return $this->filterDelegatedTasksForPerson($person, '#overdue', null, null, 'incomplete');
    }

    /**
     * Count due today delegated tasks assigned to the person.
     *
     * @param PersonEntity $person The person
     *
     * @return int
     */
    public function countDueTodayDelegatedTasksForPerson(PersonEntity $person)
    {
        return $this->filterDelegatedTasksForPerson($person, '#today', null, null, 'incomplete');
    }

    /**
     * Count due in future delegated tasks assigned to the person.
     *
     * @param PersonEntity $person The person
     *
     * @return int
     */
    public function countDueFutureDelegatedTasksForPerson(PersonEntity $person)
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

        // date where part
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

        // assignment where part
        $person->loadHelper('Agent');
        $person->loadHelper('AgentPermissions');

        $assignmentWhere = ['assigned_agent_id = ?'];

        $teamIds = $person->getHelper('Agent')->getTeamIds();
        if ($teamIds) {
            $assignmentWhere[] = 'assigned_agent_team_id IN ('.implode(',', $teamIds).')';
        }

        $departmentIds = $person->getHelper('AgentPermissions')->getAllowedDepartments('tickets', true);
        if ($departmentIds) {
            $assignmentWhere[] = 'assigned_department_id IN ('.implode(',', $departmentIds).')';
        }

        $wherePart .= ' AND ( ('.implode(' OR ', $assignmentWhere).') OR (assigned_agent_id IS NULL AND assigned_agent_team_id IS NULL AND assigned_department_id IS NULL AND person_id = ?) )';
        $params[] = $person->id;
        $params[] = $person->id;

        // state where part
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
     * @param PersonEntity $person     The person
     * @param string       $filterType
     *
     * @return Task Object
     */
    public function filterTaksForPersonTeams(PersonEntity $person, $filterType = 'total', $limit = null, $offset = null, $state = null)
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
    public function filterDelegatedTasksForPerson(PersonEntity $person, $filterType = 'total', $limit = null, $offset = null, $state = null)
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

        // date where part
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

        // assignment where part
        $wherePart .= ' AND person_id = ? ';
        $params[] = $person->id;

        $person->loadHelper('Agent');
        $person->loadHelper('AgentPermissions');

        $assignmentWhere = [
            '(assigned_agent_id IS NOT NULL AND assigned_agent_id != ?)',
        ];
        $params[] = $person->id;

        $teamIds = $person->getHelper('Agent')->getTeamIds();
        if ($teamIds) {
            $assignmentWhere[] = '(assigned_agent_team_id IS NOT NULL AND assigned_agent_team_id NOT IN ('.implode(',', $teamIds).'))';
        } else {
            $assignmentWhere[] = ' assigned_agent_team_id IS NOT NULL';
        }

        $departmentIds = $person->getHelper('AgentPermissions')->getAllowedDepartments('tickets', true);
        if ($departmentIds) {
            $assignmentWhere[] = '(assigned_department_id IS NOT NULL AND assigned_department_id NOT IN ('.implode(',', $departmentIds).'))';
        } else {
            $assignmentWhere[] = ' assigned_department_id IS NOT NULL';
        }

        $wherePart .= ' AND ('.implode(' OR ', $assignmentWhere).')';

        // state part
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
    public function filterAllPendingTasks(PersonEntity $person, $filterType = 'total', $limit = null, $offset = null, $state = null)
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

        // date where part
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

        // assignment where part
        $person->loadHelper('Agent');
        $person->loadHelper('AgentPermissions');

        $assignmentWhere = [
            'person_id = ?',
            'assigned_agent_id = ?',
        ];

        $params[] = $person->id;
        $params[] = $person->id;

        $teamIds = $person->getHelper('Agent')->getTeamIds();
        if ($teamIds) {
            $assignmentWhere[] = 'assigned_agent_team_id IN ('.implode(',', $teamIds).')';
        }

        $departmentIds = $person->getHelper('AgentPermissions')->getAllowedDepartments('tickets', true);
        if ($departmentIds) {
            $assignmentWhere[] = 'assigned_department_id IN ('.implode(',', $departmentIds).')';
        }

        $wherePart .= ' AND (('.implode(' OR ', $assignmentWhere).') OR visibility = 1)';

        // state where part
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
        $qb = $this->createQueryBuilder('t');
        $qb->select('t');
        $qb->join(Entity\TaskAssociatedTicket::class, 'ta', Join::WITH, 'ta.task = t.id');
        $qb->orderBy('t.date_due', 'ASC');

        if (!$all) {
            $qb->andWhere('t.is_completed = 0');
        }

        // get visible and own tasks
        $orAssigned = $qb->expr()->orX(
            't.visibility = 1',
            't.person = :person_id',
            't.assigned_agent = :person_id'
        );

        // get associated team tasks
        /** @var Connection $connection */
        $connection = $this->_em->getConnection();
        $teamIds    = $connection->fetchAllCol('SELECT team_id FROM agent_team_members WHERE person_id = ?', [$personContext->getId()]);
        if ($teamIds) {
            $orAssigned->add('t.assigned_agent_team IN (:team_ids)');
            $qb->setParameter('team_ids', $teamIds);
        }

        // get associated department tasks
        $personContext->loadHelper('AgentPermissions');
        /** @var AgentPermissions $helper */
        $helper = $personContext->getHelper('AgentPermissions');

        $departmentIds = $helper->getAllowedDepartments('tickets', true);
        if ($departmentIds) {
            $orAssigned->add('t.assigned_department IN (:department_ids)');
            $qb->setParameter('department_ids', $departmentIds);
        }

        $qb->andWhere($orAssigned);
        $qb->andWhere('ta.ticket = :ticket_id');
        $qb->setParameter('person_id', $personContext);
        $qb->setParameter('ticket_id', $ticket);

        return $qb->getQuery()->getResult();
    }
}

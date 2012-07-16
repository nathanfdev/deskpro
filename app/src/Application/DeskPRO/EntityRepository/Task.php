<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. http://www.deskpro.com/   |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2012, DeskPRO Ltd.             |
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

use Symfony\Component\Validator\Constraints\DateTime;

use Application\DeskPRO\App;
use \Doctrine\ORM\EntityRepository;
use Application\DeskPRO\Entity;

class Task extends EntityRepository
{
	/**
	 * Find pending tasks assigned to the person.
	 *
	 * @param Person $person The person
	 * @return array
	 */

        public function findPendingTasksForPerson(Entity\Person $person)
        {
            $qb = $this->getEntityManager()->createQueryBuilder();
            $qb->select('t')
			   ->from('DeskPRO:Task', 't')
			   ->innerJoin('t.person', 'p')
			   ->where('p.id= :person_id')
			   ->setParameters(array('person_id' => $person['id']));

            $query = $qb->getQuery();
            $tasks = $query->getResult();
            return $tasks;
        }

        /**
	 * All completed tasks.
	 *
	 * @return array of task object
	 */
	public function allCompleteTasks()
	{
		$qb = $this->getEntityManager()->createQueryBuilder();
		$qb->select('t')
		   ->from('DeskPRO:Task', 't')
		   ->innerJoin('t.person', 'p')
		   ->where('t.is_completed = :is_completed')
		   ->setParameter('is_completed', true);

		$query = $qb->getQuery();
		return $query->getResult();
	}

        /**
	 * Count completed tasks.
	 *
	 * @return int
	 */
	public function countCompleteTasks()
	{
            $qb = $this->getEntityManager()->createQueryBuilder();
			$qb->select('COUNT(t.id)')
			   ->from('DeskPRO:Task', 't')
			   ->where('t.is_completed = :is_completed')
			   ->setParameter('is_completed', true);
            $query = $qb->getQuery();
            return $query->getSingleScalarResult();
	}

	/**
	 * Count pending tasks.
	 *
	 * @return int
	 */
	public function countPendingTasks(Entity\Person $person)
	{
		$qb = $this->getEntityManager()->createQueryBuilder();
		$qb->select('COUNT(t.id)')
		   ->from('DeskPRO:Task', 't')
		   ->where('t.is_completed = :is_completed')
		   ->setParameter('is_completed', false);

		$person->loadHelper('Agent');
		if ($person->Agent->getTeamIds()) {
			$qb->andWhere('(t.person = :person OR t.assigned_agent = :person OR t.assigned_agent_team IN (:agent_teams)) OR t.visibility = 1');
			$qb->setParameter('person', $person);
			$qb->setParameter('agent_teams', $person->Agent->getTeamIds());
		} else {
			$qb->andWhere('(t.person = :person OR t.assigned_agent = :person) OR t.visibility =1');
			$qb->setParameter('person', $person);
		}

		$query = $qb->getQuery();
		return $query->getSingleScalarResult();
	}

	/**
	 * Count overdue tasks.
	 *
	 * @param string $time_zone The time zone
	 * @return int
	 */
	public function countOverdueTasks(Entity\Person $person)
	{
		$date = new \DateTime('now', $person->getDateTimezone());

		$qb = $this->getEntityManager()->createQueryBuilder();
		$qb->select('COUNT(t.id)')
		   ->from('DeskPRO:Task', 't')
		   ->where('t.is_completed = :is_completed')
		   ->andWhere('t.date_due < :date_due')
		   ->setParameter('is_completed', false)
		   ->setParameter('date_due', $date);

		$person->loadHelper('Agent');
		if ($person->Agent->getTeamIds()) {
			$qb->andWhere('(t.person = :person OR t.assigned_agent = :person OR t.assigned_agent_team IN (:agent_teams)) OR t.visibility = 1');
			$qb->setParameter('person', $person);
			$qb->setParameter('agent_teams', $person->Agent->getTeamIds());
		} else {
			$qb->andWhere('(t.person = :person OR t.assigned_agent = :person) OR t.visibility = 1');
			$qb->setParameter('person', $person);
		}

		$query = $qb->getQuery();
		return $query->getSingleScalarResult();
	}

	/**
	 * Count due today tasks.
	 *
	 * @param string $time_zone The time zone
	 * @return int
	 */
	public function countDueTodayTasks(Entity\Person $person)
	{
		$time_zone = $person->getDateTimezone();
		$today = new \DateTime('today', $time_zone);
		$tomorrow = new \DateTime('tomorrow', $time_zone);

		$qb = $this->getEntityManager()->createQueryBuilder();
		$qb->select('COUNT(t.id)')
		   ->from('DeskPRO:Task', 't')
		   ->andWhere('(t.date_due >= :today AND t.date_due < :tomorrow) OR t.date_due IS NULL')
		   ->andWhere('t.is_completed = :is_completed');

		$person->loadHelper('Agent');
		if ($person->Agent->getTeamIds()) {
			$qb->andWhere('(t.person = :person OR t.assigned_agent = :person OR t.assigned_agent_team IN (:agent_teams)) OR t.visibility = 1');
			$qb->setParameter('person', $person);
			$qb->setParameter('agent_teams', $person->Agent->getTeamIds());
		} else {
			$qb->andWhere('(t.person = :person OR t.assigned_agent = :person) OR t.visibility = 1');
			$qb->setParameter('person', $person);
		}

		$qb->setParameters(array(
			'is_completed' => false,
			'today' => $today,
			'tomorrow' => $tomorrow
		));

		$query = $qb->getQuery();
		return $query->getSingleScalarResult();
	}

        /**
	 * Count due in future tasks.
	 *
	 * @param string $time_zone The time zone
	 * @return int
	 */
	public function countDueFutureTasks(Entity\Person $person)
	{
		$time_zone = $person->getDateTimezone();
		$today = new \DateTime('today', $time_zone);

		$qb = $this->getEntityManager()->createQueryBuilder();
		$qb->select('COUNT(t.id)')
		   ->from('DeskPRO:Task', 't')
		   ->andWhere('t.date_due > :today')
		   ->andWhere('t.is_completed = :is_completed');

		$person->loadHelper('Agent');
		if ($person->Agent->getTeamIds()) {
			$qb->andWhere('(t.person = :person OR t.assigned_agent = :person OR t.assigned_agent_team IN (:agent_teams)) OR t.visibility = 1');
			$qb->setParameter('person', $person);
			$qb->setParameter('agent_teams', $person->Agent->getTeamIds());
		} else {
			$qb->andWhere('(t.person = :person OR t.assigned_agent = :person) OR t.visibility = 1');
			$qb->setParameter('person', $person);
		}

		$qb->setParameters(array(
			'is_completed' => false,
			'today' => $today,
		));

		$query = $qb->getQuery();
		return $query->getSingleScalarResult();
	}

	/**
	 * Count pending tasks assigned to the person.
	 *
	 * @param Person $person The person
	 * @return int
	 */
	public function countPendingTasksForPerson(Entity\Person $person)
	{
		$qb = $this->getEntityManager()->createQueryBuilder();
		$qb->select('COUNT(t.id)')
		   ->from('DeskPRO:Task', 't')
		   ->innerJoin('t.person', 'p')
		   ->leftJoin('t.assigned_agent', 'aa')
		   ->leftJoin('t.assigned_agent_team', 'at')
		   ->andWhere('p.id = :person_id AND aa.id IS NULL AND at.id IS NULL')
		   ->orWhere('aa.id = :person_id')
		   ->andWhere('t.is_completed = :is_completed');

		$qb->setParameters(array(
			'person_id'=> $person['id'],
			'is_completed'=> false
		));

		$query = $qb->getQuery();
		return $query->getSingleScalarResult();
	}

	/**
	 * Count overdue tasks assigned to the person.
	 *
	 * @param Person $person The person
	 * @return int
	 */
        public function countOverdueTasksForPerson(Entity\Person $person)
        {
            $date = new \DateTime('now', $person->getDateTimezone());

            $qb = $this->getEntityManager()->createQueryBuilder();
            $qb->select('COUNT(t.id)')
			   ->from('DeskPRO:Task', 't')
			   ->innerJoin('t.person', 'p')
			   ->leftJoin('t.assigned_agent', 'aa')
			   ->leftJoin('t.assigned_agent_team', 'at')
			   ->andWhere('p.id = :person_id AND aa.id IS NULL AND at.id IS NULL')
			   ->orWhere('aa.id = :person_id')
			   ->andWhere('t.is_completed = :is_completed')
			   ->andWhere('t.date_due < :date_due')
			   ->setParameter('person_id', $person['id'])
			   ->setParameter('is_completed', false)
			   ->setParameter('date_due', $date,\Doctrine\DBAL\Types\Type::DATETIME);

            $query = $qb->getQuery();
            return $query->getSingleScalarResult();
    }

	/**
	 * Count due today tasks assigned to the person.
	 *
	 * @param Person $person The person
	 * @return int
	 */
	public function countDueTodayTasksForPerson(Entity\Person $person)
	{
		$time_zone = $person->getDateTimezone();
		$today = new \DateTime('today', $time_zone);
		$tomorrow = new \DateTime('tomorrow', $time_zone);

		$qb = $this->getEntityManager()->createQueryBuilder();

		$qb->select('COUNT(t.id)')
		   ->from('DeskPRO:Task', 't')
		   ->innerJoin('t.person', 'p')
		   ->leftJoin('t.assigned_agent', 'aa')
		   ->leftJoin('t.assigned_agent_team', 'at')
		   ->andWhere('p.id = :person_id AND aa.id IS NULL AND at.id IS NULL')
		   ->orWhere('aa.id = :person_id')
		   ->andWhere('t.is_completed = :is_completed')
		   ->andWhere('(t.date_due >= :today AND t.date_due < :tomorrow) OR t.date_due IS NULL');

		$qb->setParameters(array(
			'person_id' => $person['id'],
			'is_completed' => false,
			'today' => $today,
			'tomorrow'=> $tomorrow
		));
		$query = $qb->getQuery();
		return $query->getSingleScalarResult();
	}


        /**
	 * Count due in future tasks assigned to the person.
	 *
	 * @param Person $person The person
	 * @return int
	 */
	public function countDueFutureTasksForPerson(Entity\Person $person)
	{
		$time_zone = $person->getDateTimezone();
		$today = new \DateTime('today', $time_zone);

		$qb = $this->getEntityManager()->createQueryBuilder();

		$qb->select('COUNT(t.id)')
		   ->from('DeskPRO:Task', 't')
		   ->innerJoin('t.person', 'p')
		   ->leftJoin('t.assigned_agent', 'aa')
		   ->leftJoin('t.assigned_agent_team', 'at')
		   ->andWhere('p.id = :person_id AND aa.id IS NULL AND at.id IS NULL')
		   ->orWhere('aa.id = :person_id')
		   ->andWhere('t.is_completed = :is_completed')
		   ->andWhere('t.date_due > :today ');

		$qb->setParameters(array(
			'person_id' => $person['id'],
			'is_completed' => false,
			'today' => $today,
		));
		$query = $qb->getQuery();
		return $query->getSingleScalarResult();
	}

	/**
	 * Count all pending tasks assigned to the person's teams.
	 *
	 * @param Entity\Person $person The person
	 * @return int
	 */
	public function countPendingTaksForPersonTeams(Entity\Person $person)
	{
		$query = $this->getEntityManager()->createQuery("
			SELECT COUNT(t.id)
			FROM DeskPRO:Task t
			JOIN t.assigned_agent_team at
			JOIN at.members m
			WHERE m.id = ?1
			AND t.is_completed = false
		");

		return $query->setParameter(1, $person['id'])->getSingleScalarResult();
	}

	/**
	 * Count overdue tasks assigned to the perso's teams.
	 *
	 * @param Person $person The person
	 * @return int
	 */
	public function countOverdueTasksForPersonTeams(Entity\Person $person)
	{
		$query = $this->getEntityManager()->createQuery("
			SELECT COUNT(t.id)
			FROM DeskPRO:Task t
			JOIN t.assigned_agent_team at
			JOIN at.members m
			WHERE m.id = ?1
			AND t.is_completed = false
			AND t.date_due < ?2
		");

		$date = new \DateTime('now', $person->getDateTimezone());

		return $query->setParameter(1, $person['id'])
			->setParameter(2, $date, \Doctrine\DBAL\Types\Type::DATETIME)
			->getSingleScalarResult();
	}

	/**
	 * Count due today tasks assigned to the person's teamsT.
	 *
	 * @param Person $person The person
	 * @return int
	 */
	public function countDueTodayTasksForPersonTeams(Entity\Person $person)
	{
		$query = $this->getEntityManager()->createQuery("
			SELECT COUNT(t.id)
			FROM DeskPRO:Task t
			JOIN t.assigned_agent_team at
			JOIN at.members m
			WHERE m.id = :person_id
			AND t.is_completed = false
			AND (
				(t.date_due >= :today AND t.date_due < :tomorrow)
				OR t.date_due IS NULL
			)
		");

		$time_zone = $person->getDateTimezone();
		$today = new \DateTime('today', $time_zone);
		$tomorrow = new \DateTime('tomorrow', $time_zone);

		return $query->setParameter('person_id', $person['id'])
			->setParameter('today', $today, \Doctrine\DBAL\Types\Type::DATETIME)
			->setParameter('tomorrow', $tomorrow, \Doctrine\DBAL\Types\Type::DATETIME)
			->getSingleScalarResult();
	}

        /**
	 * Count due in future tasks assigned to the person's teams.
	 *
	 * @param Person $person The person
	 * @return int
	 */
	public function countDueFutureTasksForPersonTeams(Entity\Person $person)
	{
		$query = $this->getEntityManager()->createQuery("
			SELECT COUNT(t.id)
			FROM DeskPRO:Task t
			JOIN t.assigned_agent_team at
			JOIN at.members m
			WHERE m.id = :person_id
			AND t.is_completed = false
			AND t.date_due >= :today
		");

		$time_zone = $person->getDateTimezone();
		$today = new \DateTime('today', $time_zone);

		return $query->setParameter('person_id', $person['id'])
			->setParameter('today', $today, \Doctrine\DBAL\Types\Type::DATETIME)
			->getSingleScalarResult();
	}


	/**
	 * Count pending delegated tasks assigned to the person.
	 *
	 * @param Person $person The person
	 * @return int
	 */
	public function countPendingDelegatedTasksForPerson(Entity\Person $person)
	{
		$qb = $this->getEntityManager()->createQueryBuilder();
		$qb->select('COUNT(t.id)')
			->from('DeskPRO:Task', 't')
			->innerJoin('t.person', 'p')
			->leftJoin('t.assigned_agent', 'aa')
			->where('p.id= :person_id AND aa.id IS NOT NULL AND aa.id != :person_id AND t.is_completed = :is_completed')
			->setParameter('person_id', $person['id'])
			->setParameter('is_completed', false)
			;
		$query = $qb->getQuery();//print $query->getSQL(); exit;
		return $query->getSingleScalarResult();
	}

	/**
	 * Count overdue delegated tasks assigned to the person.
	 *
	 * @param Person $person The person
	 * @return int
	 */
	public function countOverdueDelegatedTasksForPerson(Entity\Person $person)
	{

		$date = new \DateTime('now', $person->getDateTimezone());

		$qb = $this->getEntityManager()->createQueryBuilder();
		$qb->select('COUNT(t.id)')
		   ->from('DeskPRO:Task', 't')
		   ->innerJoin('t.person', 'p')
		   ->leftJoin('t.assigned_agent', 'aa')
		   ->where('p.id= :person_id')
		   ->andWhere('aa.id IS NOT NULL')
		   ->andWhere('aa.id != :person_id')
		   ->andWhere('t.is_completed = :is_completed AND t.date_due < :date_due')
		   ->setParameter('person_id', $person['id'])
		   ->setParameter('is_completed', false)
		   ->setParameter('date_due', $date, \Doctrine\DBAL\Types\Type::DATETIME);

		$query = $qb->getQuery();
		return $query->getSingleScalarResult();
	}

	/**
	 * Count due today delegated tasks assigned to the person.
	 *
	 * @param Person $person The person
	 * @return int
	 */
	public function countDueTodayDelegatedTasksForPerson(Entity\Person $person)
	{
		$time_zone = $person->getDateTimezone();
		$today = new \DateTime('today', $time_zone);
		$tomorrow = new \DateTime('tomorrow', $time_zone);
		$qb = $this->getEntityManager()->createQueryBuilder();
		$qb->select('COUNT(t.id)')
		   ->from('DeskPRO:Task', 't')
		   ->innerJoin('t.person', 'p')
		   ->leftJoin('t.assigned_agent', 'aa')
		   ->where('p.id= :person_id')
		   ->andWhere('aa.id IS NOT NULL')
		   ->andWhere('aa.id != :person_id')
		   ->andWhere('t.is_completed = :is_completed ')
		   ->andWhere('(t.date_due >= :today AND t.date_due < :tomorrow) OR t.date_due IS NULL')
		   //->orWhere('t.date_due IS NULL')
		   ->setParameter('person_id', $person['id'])
		   ->setParameter('is_completed', false)
		   ->setParameter('today', $today, \Doctrine\DBAL\Types\Type::DATETIME)
		   ->setParameter('tomorrow', $tomorrow, \Doctrine\DBAL\Types\Type::DATETIME);

		$query = $qb->getQuery();
		return $query->getSingleScalarResult();
	}

        /**
	 * Count due in future delegated tasks assigned to the person.
	 *
	 * @param Person $person The person
	 * @return int
	 */
	public function countDueFutureDelegatedTasksForPerson(Entity\Person $person)
	{
		$time_zone = $person->getDateTimezone();
		$today = new \DateTime('today', $time_zone);

		$qb = $this->getEntityManager()->createQueryBuilder();
		$qb->select('COUNT(t.id)')
		   ->from('DeskPRO:Task', 't')
		   ->innerJoin('t.person', 'p')
		   ->leftJoin('t.assigned_agent', 'aa')
		   ->where('p.id= :person_id')
		   ->andWhere('aa.id IS NOT NULL')
		   ->andWhere('aa.id != :person_id')
		   ->andWhere('t.is_completed = :is_completed ')
		   ->andWhere('t.date_due > :today')
		   ->setParameter('person_id', $person['id'])
		   ->setParameter('is_completed', false)
		   ->setParameter('today', $today, \Doctrine\DBAL\Types\Type::DATETIME);

		$query = $qb->getQuery();
		return $query->getSingleScalarResult();
	}

        /**
	 * All pending tasks assigned to the person.
	 *
	 * @param Person $person The person
         * @param string $filter_type
	 * @return task object
	 */
	public function filterTasksForPerson(Entity\Person $person, $filter_type = 'total')
	{
		$time_zone = $person->getDateTimezone();
		$today = new \DateTime('today', $time_zone);
		$tomorrow = new \DateTime('tomorrow', $time_zone);
		$date = new \DateTime('now', $time_zone);

		$qb = $this->getEntityManager()->createQueryBuilder();
		$qb->select('t');
		$qb->from('DeskPRO:Task', 't');
		$qb->innerJoin('t.person', 'p');
		$qb->leftJoin('t.assigned_agent', 'aa');
		$qb->leftJoin('t.assigned_agent_team', 'at');
		$qb->andWhere('p.id = :person_id AND aa.id IS NULL AND at.id IS NULL');
		$qb->orWhere('aa.id = :person_id');
		$qb->orderBy('t.id', 'DESC');

		if ($filter_type == 'today') {
			$qb->andWhere('(t.date_due >= :today AND t.date_due < :tomorrow) OR t.date_due IS NULL');
			$qb->setParameter('today', $today);
			$qb->setParameter('tomorrow', $tomorrow);

		} elseif ($filter_type == 'future') {
			$qb->andWhere('t.date_due > :today ');
			$qb->setParameter('today', $today);

		} elseif ($filter_type == 'overdue') {
			$qb->andWhere('t.date_due < :date_due');
			$qb->setParameter('date_due', $date,\Doctrine\DBAL\Types\Type::DATETIME);
		}

		$qb->setParameters(array(
			'person_id'=> $person['id'],
		));
		$query = $qb->getQuery();
		return $query->getResult();
	}

        /**
	 * All pending tasks assigned to the person's teams.
	 *
	 * @param Entity\Person $person The person
         * @param string $filter_type
	 * @return Task Object
	 */
	public function filterTaksForPersonTeams(Entity\Person $person, $filter_type = 'total')
	{
		$time_zone = $person->getDateTimezone();
		$today = new \DateTime('today', $time_zone);
		$tomorrow = new \DateTime('tomorrow', $time_zone);
		$date = new \DateTime('now', $time_zone);

		$qb = $this->getEntityManager()->createQueryBuilder();
		$qb->select('t');
		$qb->from('DeskPRO:Task', 't');
		$qb->innerJoin('t.assigned_agent_team', 'aat');
		$qb->innerJoin('aat.members', 'm');
		$qb->where('m.id = :person_id');
		$qb->orderBy('t.id', 'DESC');

		if($filter_type == 'today')
		{
			$qb->andWhere('(t.date_due >= :today AND t.date_due < :tomorrow) OR t.date_due IS NULL');
			$qb->setParameter('today', $today);
			$qb->setParameter('tomorrow', $tomorrow);
		}else if($filter_type == 'future')
		{
			$qb->andWhere('t.date_due > :today ');
			$qb->setParameter('today', $today);
		}else if($filter_type == 'overdue')
		{
			$qb->andWhere('t.date_due < :date_due');
			$qb->setParameter('date_due', $date,\Doctrine\DBAL\Types\Type::DATETIME);
		}

		$qb->setParameters(array('person_id'=> $person['id']));

		$query = $qb->getQuery();
		return $query->getResult();
	}

        /**
	 * Count pending delegated tasks assigned to the person.
	 *
	 * @param Person $person The person
	 * @return int
	 */
	public function filterDelegatedTasksForPerson(Entity\Person $person, $filter_type = 'total')
	{
		$time_zone = $person->getDateTimezone();
		$today = new \DateTime('today', $time_zone);
		$tomorrow = new \DateTime('tomorrow', $time_zone);
		$date = new \DateTime('now', $time_zone);

		$qb = $this->getEntityManager()->createQueryBuilder();
		$qb->select('t');
		$qb->from('DeskPRO:Task', 't');
		$qb->innerJoin('t.person', 'p');
		$qb->leftJoin('t.assigned_agent', 'aa');
		$qb->where('p.id= :person_id');
		$qb->andWhere('aa.id IS NOT NULL');
		$qb->andWhere('aa.id != :person_id');
		$qb->orderBy('t.id', 'DESC');

		if($filter_type == 'today') {
			$qb->andWhere('(t.date_due >= :today AND t.date_due < :tomorrow) OR t.date_due IS NULL');
			$qb->setParameter('today', $today);
			$qb->setParameter('tomorrow', $tomorrow);
		} elseif ($filter_type == 'future') {
			$qb->andWhere('t.date_due > :today ');
			$qb->setParameter('today', $today);
		} elseif ($filter_type == 'overdue') {
			$qb->andWhere('t.date_due < :date_due');
			$qb->setParameter('date_due', $date,\Doctrine\DBAL\Types\Type::DATETIME);
		}

		$qb->setParameters(array('person_id'=> $person['id']));
		$query = $qb->getQuery();
		return $query->getResult();
	}

        /**
	 * Filter all pending tasks.
	 *
         * @param string $filter_type
	 * @return int
	 */
	public function filterAllPendingTasks($person, $filter_type = 'total')
	{
		$time_zone = $person->getDateTimezone();
		$today = new \DateTime('today', $time_zone);
		$tomorrow = new \DateTime('tomorrow', $time_zone);
		$date = new \DateTime('now', $time_zone);

		$qb = $this->getEntityManager()->createQueryBuilder();
		$qb->select('t');
		$qb->from('DeskPRO:Task', 't');
		$qb->innerJoin('t.person', 'p');
		$qb->orderBy('t.id', 'DESC');

		if($filter_type == 'today') {
			$qb->andWhere('(t.date_due >= :today AND t.date_due < :tomorrow) OR t.date_due IS NULL');
			$qb->setParameter('today', $today);
			$qb->setParameter('tomorrow', $tomorrow);
		} elseif($filter_type == 'future') {
			$qb->andWhere('t.date_due > :today ');
			$qb->setParameter('today', $today);
		} elseif($filter_type == 'overdue') {
			$qb->andWhere('t.date_due < :date_due');
			$qb->setParameter('date_due', $date,\Doctrine\DBAL\Types\Type::DATETIME);
		}

		$person->loadHelper('Agent');
		if ($person->Agent->getTeamIds()) {
			$qb->andWhere('(t.person = :person OR t.assigned_agent = :person OR t.assigned_agent_team IN (:agent_teams)) OR t.visibility = 1');
			$qb->setParameter('person', $person);
			$qb->setParameter('agent_teams', $person->Agent->getTeamIds());
		} else {
			$qb->andWhere('(t.person = :person OR t.assigned_agent = :person) OR t.visibility = 1');
			$qb->setParameter('person', $person);
		}

		$query = $qb->getQuery();
		return $query->getResult();
	}
}

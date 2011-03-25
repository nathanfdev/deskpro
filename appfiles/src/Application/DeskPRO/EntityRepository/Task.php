<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @category Entities
 * @copyright Copyright (c) 2011 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Ricardo Rauch <ricardo@gravityonmars.com>
 */

namespace Application\DeskPRO\EntityRepository;

use Symfony\Component\Validator\Constraints\DateTime;

use \Application\DeskPRO\App;
use \Doctrine\ORM\EntityRepository;
use \Application\DeskPRO\Entity;

class Task extends EntityRepository
{

	/**
	 * Find pending tasks assigned to the person.
	 *
	 * @param Person $person The person
	 * @return Array
	 */
	public function findPendingTasksForPerson(Entity\Person $person)
	{
		$query = $this->getEntityManager()->createQuery("
			SELECT t
			FROM DeskPRO:Task t
			WHERE 
			(
					(t.person_id = ?1 AND t.assigned_agent_id IS NULL)
				OR
					t.assigned_agent_id = ?1
			)
			AND t.is_completed = false
			ORDER BY t.date_due ASC
		");
		
		return $query->setParameter(1, $person['id'])->getResult();
	}
	
	/**
	 * Count pending tasks.
	 *
	 * @return int
	 */
	public function countPendingTasks()
	{
		$query = $this->getEntityManager()->createQuery("
			SELECT COUNT(t.id)
			FROM DeskPRO:Task t
			WHERE t.is_completed = false
		");
		
		return $query->getSingleScalarResult();
	}
	
	/**
	 * Count overdue tasks.
	 *
	 * @param string $time_zone The time zone
	 * @return int
	 */
	public function countOverdueTasks($time_zone)
	{
		$query = $this->getEntityManager()->createQuery("
			SELECT COUNT(t.id)
			FROM DeskPRO:Task t
			WHERE t.is_completed = false
			AND t.date_due < ?1
		");
		
		$date = new \DateTime('now', new \DateTimeZone($time_zone));
		
		return $query->setParameter(1, $date, \Doctrine\DBAL\Types\Type::DATETIME)
			->getSingleScalarResult();
	}
	
	/**
	 * Count due today tasks.
	 *
	 * @param string $time_zone The time zone
	 * @return int
	 */
	public function countDueTodayTasks($time_zone)
	{
		$query = $this->getEntityManager()->createQuery("
			SELECT COUNT(t.id)
			FROM DeskPRO:Task t
			WHERE t.is_completed = false
			AND (
				(t.date_due >= :today AND t.date_due < :tomorrow)
				OR t.date_due IS NULL
			)
		");
		
		$time_zone = new \DateTimeZone($time_zone);
		$today = new \DateTime('today', $time_zone);
		$tomorrow = new \DateTime('tomorrow', $time_zone);
		
		return $query->setParameter('today', $today, \Doctrine\DBAL\Types\Type::DATETIME)
			->setParameter('tomorrow', $tomorrow, \Doctrine\DBAL\Types\Type::DATETIME)
			->getSingleScalarResult();
	}
	
	/**
	 * Count pending tasks assigned to the person.
	 *
	 * @param Person $person The person
	 * @return int
	 */
	public function countPendingTasksForPerson(Entity\Person $person)
	{
		$query = $this->getEntityManager()->createQuery("
			SELECT COUNT(t.id)
			FROM DeskPRO:Task t
			WHERE 
			(
					(t.person_id = ?1 AND t.assigned_agent_id IS NULL)
				OR
					t.assigned_agent_id = ?1
			)
			AND t.is_completed = false
		");
		
		return $query->setParameter(1, $person['id'])->getSingleScalarResult();
	}
	
	/**
	 * Count overdue tasks assigned to the person.
	 *
	 * @param Person $person The person
	 * @return int
	 */
	public function countOverdueTasksForPerson(Entity\Person $person)
	{
		$query = $this->getEntityManager()->createQuery("
			SELECT COUNT(t.id)
			FROM DeskPRO:Task t
			WHERE
			(
					(t.person_id = ?1 AND t.assigned_agent_id IS NULL)
				OR
					t.assigned_agent_id = ?1
			)
			AND t.is_completed = false
			AND t.date_due < ?2
		");
		
		$date = new \DateTime('now', new \DateTimeZone($person['timezone']));
		
		return $query->setParameter(1, $person['id'])
			->setParameter(2, $date, \Doctrine\DBAL\Types\Type::DATETIME)
			->getSingleScalarResult();
	}
	
	/**
	 * Count due today tasks assigned to the person.
	 *
	 * @param Person $person The person
	 * @return int
	 */
	public function countDueTodayTasksForPerson(Entity\Person $person)
	{
		$query = $this->getEntityManager()->createQuery("
			SELECT COUNT(t.id)
			FROM DeskPRO:Task t
			WHERE
			(
					(t.person_id = :person_id AND t.assigned_agent_id IS NULL)
				OR
					t.assigned_agent_id = :person_id
			)
			AND t.is_completed = false
			AND (
				(t.date_due >= :today AND t.date_due < :tomorrow)
				OR t.date_due IS NULL
			)
		");
		
		$time_zone = new \DateTimeZone($person['timezone']);
		$today = new \DateTime('today', $time_zone);
		$tomorrow = new \DateTime('tomorrow', $time_zone);
		
		return $query->setParameter('person_id', $person['id'])
			->setParameter('today', $today, \Doctrine\DBAL\Types\Type::DATETIME)
			->setParameter('tomorrow', $tomorrow, \Doctrine\DBAL\Types\Type::DATETIME)
			->getSingleScalarResult();
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
		
		$date = new \DateTime('now', new \DateTimeZone($person['timezone']));
		
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
		
		$time_zone = new \DateTimeZone($person['timezone']);
		$today = new \DateTime('today', $time_zone);
		$tomorrow = new \DateTime('tomorrow', $time_zone);
		
		return $query->setParameter('person_id', $person['id'])
			->setParameter('today', $today, \Doctrine\DBAL\Types\Type::DATETIME)
			->setParameter('tomorrow', $tomorrow, \Doctrine\DBAL\Types\Type::DATETIME)
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
		$query = $this->getEntityManager()->createQuery("
			SELECT COUNT(t.id)
			FROM DeskPRO:Task t
			WHERE t.person_id = ?1
            AND t.assigned_agent_id IS NOT NULL
            AND t.assigned_agent_id != ?1
			AND t.is_completed = false
		");
		
		return $query->setParameter(1, $person['id'])->getSingleScalarResult();
	}
	
	/**
	 * Count overdue delegated tasks assigned to the person.
	 *
	 * @param Person $person The person
	 * @return int
	 */
	public function countOverdueDelegatedTasksForPerson(Entity\Person $person)
	{
		$query = $this->getEntityManager()->createQuery("
			SELECT COUNT(t.id)
			FROM DeskPRO:Task t
			WHERE t.person_id = ?1
            AND t.assigned_agent_id IS NOT NULL
            AND t.assigned_agent_id != ?1
			AND t.is_completed = false
			AND t.date_due < ?2
		");
		
		$date = new \DateTime('now', new \DateTimeZone($person['timezone']));
		
		return $query->setParameter(1, $person['id'])
			->setParameter(2, $date, \Doctrine\DBAL\Types\Type::DATETIME)
			->getSingleScalarResult();
	}
	
	/**
	 * Count due today delegated tasks assigned to the person.
	 *
	 * @param Person $person The person
	 * @return int
	 */
	public function countDueTodayDelegatedTasksForPerson(Entity\Person $person)
	{
		$query = $this->getEntityManager()->createQuery("
			SELECT COUNT(t.id)
			FROM DeskPRO:Task t
			WHERE t.person_id = :person_id
            AND t.assigned_agent_id IS NOT NULL
			AND t.assigned_agent_id != :person_id
			AND t.is_completed = false
			AND (
				(t.date_due >= :today AND t.date_due < :tomorrow)
				OR t.date_due IS NULL
			)
		");
		
		$time_zone = new \DateTimeZone($person['timezone']);
		$today = new \DateTime('today', $time_zone);
		$tomorrow = new \DateTime('tomorrow', $time_zone);
		
		return $query->setParameter('person_id', $person['id'])
			->setParameter('today', $today, \Doctrine\DBAL\Types\Type::DATETIME)
			->setParameter('tomorrow', $tomorrow, \Doctrine\DBAL\Types\Type::DATETIME)
			->getSingleScalarResult();
	}
	
}
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

use Application\DeskPRO\App;
use \Doctrine\ORM\EntityRepository;
use Application\DeskPRO\Entity;

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
            $qb = $this->getEntityManager()->createQueryBuilder();
            $qb->select('t')
                    ->from('DeskPRO:Task', 't')
                    ->innerJoin('t.person', 'p')
                    ->where('p.id= :person_id')
                    ->setParameters(array('person_id' => $person['id']))
            ;
            $query = $qb->getQuery();
            $tasks = $query->getResult();
            return $tasks;
        }

	/**
	 * Count pending tasks.
	 *
	 * @return int
	 */
	public function countPendingTasks()
	{
            $qb = $this->getEntityManager()->createQueryBuilder();
            $qb->select('COUNT(t.id)')
                    ->from('DeskPRO:Task', 't')
                    ->where('t.is_completed = :is_completed')
                    ->setParameter('is_completed', false)
                    ;
            $query = $qb->getQuery();
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
            $date = new \DateTime('now', new \DateTimeZone($time_zone));

            $qb = $this->getEntityManager()->createQueryBuilder();
            $qb->select('COUNT(t.id)')
                    ->from('DeskPRO:Task', 't')
                    ->where('t.is_completed = :is_completed')
                    ->andWhere('t.date_due < :date_due')
                    ->setParameters(array('is_completed' => false, 'date_due' => $date))
                    ;
            $query = $qb->getQuery();
            return $query->getSingleScalarResult();
	}

	/**
	 * Count due today tasks.
	 *
	 * @param string $time_zone The time zone
	 * @return int
	 */
	public function countDueTodayTasks($time_zone)
	{
            $time_zone = new \DateTimeZone($time_zone);
            $today = new \DateTime('today', $time_zone);
            $tomorrow = new \DateTime('tomorrow', $time_zone);

            $qb = $this->getEntityManager()->createQueryBuilder();
            $qb->select('COUNT(t.id)')
                    ->from('DeskPRO:Task', 't')                    
                    ->andWhere('t.date_due >= :today AND t.date_due < :tomorrow')
                    ->orWhere('t.date_due IS NULL')
                    ->andWhere('t.is_completed = :is_completed')
                    
                    ->setParameters(array('is_completed' => false, 'today' => $today, 'tomorrow' => $tomorrow))
                    ;
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
                    ->andWhere('p.id = :person_id AND aa.id IS NULL')
                    ->orWhere('aa.id = :person_id')
                    ->andWhere('t.is_completed = :is_completed')
                    ->setParameters(array('person_id'=> $person['id'], 'is_completed'=> false))                    
                    ;
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

            $date = new \DateTime('now', new \DateTimeZone($person['timezone']));

            $qb = $this->getEntityManager()->createQueryBuilder();
            $qb->select('COUNT(t.id)')
                    ->from('DeskPRO:Task', 't')
                    ->innerJoin('t.person', 'p')
                    ->leftJoin('t.assigned_agent', 'aa')
                    ->where('p.id = :person_id AND aa.id IS NULL')
                    ->orWhere('aa.id = :person_id')
                    ->andWhere('t.is_completed = :is_completed')
                    ->andWhere('t.date_due < :date_due')
                    ->setParameter('person_id', $person['id'])
                    ->setParameter('is_completed', false)
                    ->setParameter('date_due', $date,\Doctrine\DBAL\Types\Type::DATETIME)
            ;

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
            $time_zone = new \DateTimeZone($person['timezone']);
            $today = new \DateTime('today', $time_zone);
            $tomorrow = new \DateTime('tomorrow', $time_zone);

            $qb = $this->getEntityManager()->createQueryBuilder();

            $qb->select('COUNT(t.id)')
                ->from('DeskPRO:Task', 't')
                ->innerJoin('t.person', 'p')
                ->leftJoin('t.assigned_agent', 'aa')
                ->andWhere('p.id = :person_id AND aa.id IS NULL')
                ->orWhere('aa.id = :person_id')
                ->andWhere('t.is_completed = :is_completed')
                ->andWhere('(t.date_due >= :today AND t.date_due < :tomorrow) OR t.date_due IS NULL')
                ->setParameters(array(
                    'person_id' => $person['id'],
                    'is_completed' => false,
                    'today' => $today,
                    'tomorrow'=> $tomorrow
                    ))
            ;
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
//		$query = $this->getEntityManager()->createQuery("
//			SELECT COUNT(t.id)
//			FROM DeskPRO:Task t
//			WHERE t.person_id = ?1
//                        AND t.assigned_agent_id IS NOT NULL
//                        AND t.assigned_agent_id != ?1
//			AND t.is_completed = false
//		");

		//return $query->setParameter(1, $person['id'])->getSingleScalarResult();

                $qb = $this->getEntityManager()->createQueryBuilder();
                $qb->select('COUNT(t.id)')
                    ->from('DeskPRO:Task', 't')
                    ->innerJoin('t.person', 'p')
                    ->leftJoin('t.assigned_agent', 'aa')
                    ->where('p.id= :person_id AND aa.id IS NOT NULL AND aa.id != :person_id AND t.is_completed = :is_completed')
//                    ->where('aa.id IS NOT NULL')
//                    ->where('aa.id != :person_id')
//                    ->where('t.is_completed = :is_completed')
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
//		$query = $this->getEntityManager()->createQuery("
//			SELECT COUNT(t.id)
//			FROM DeskPRO:Task t
//			WHERE t.person_id = ?1
//            AND t.assigned_agent_id IS NOT NULL
//            AND t.assigned_agent_id != ?1
//			AND t.is_completed = false
//			AND t.date_due < ?2
//		");

		$date = new \DateTime('now', new \DateTimeZone($person['timezone']));

//		return $query->setParameter(1, $person['id'])
//			->setParameter(2, $date, \Doctrine\DBAL\Types\Type::DATETIME)
//			->getSingleScalarResult();

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
                    ->setParameter('date_due', $date, \Doctrine\DBAL\Types\Type::DATETIME)
                    ;
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
//		$query = $this->getEntityManager()->createQuery("
//			SELECT COUNT(t.id)
//			FROM DeskPRO:Task t
//			WHERE t.person_id = :person_id
//            AND t.assigned_agent_id IS NOT NULL
//			AND t.assigned_agent_id != :person_id
//			AND t.is_completed = false
//			AND (
//				(t.date_due >= :today AND t.date_due < :tomorrow)
//				OR t.date_due IS NULL
//			)
//		");

		$time_zone = new \DateTimeZone($person['timezone']);
		$today = new \DateTime('today', $time_zone);
		$tomorrow = new \DateTime('tomorrow', $time_zone);

//		return $query->setParameter('person_id', $person['id'])
//			->setParameter('today', $today, \Doctrine\DBAL\Types\Type::DATETIME)
//			->setParameter('tomorrow', $tomorrow, \Doctrine\DBAL\Types\Type::DATETIME)
//			->getSingleScalarResult();


                $qb = $this->getEntityManager()->createQueryBuilder();
                $qb->select('COUNT(t.id)')
                    ->from('DeskPRO:Task', 't')
                    ->innerJoin('t.person', 'p')
                    ->leftJoin('t.assigned_agent', 'aa')
                    ->where('p.id= :person_id')
                    ->andWhere('aa.id IS NOT NULL')
                    ->andWhere('aa.id != :person_id')
                    ->andWhere('t.is_completed = :is_completed ')
                    ->andWhere('t.date_due >= :today AND t.date_due < :tomorrow')
                    ->orWhere('t.date_due IS NULL')
                    ->setParameter('person_id', $person['id'])
                    ->setParameter('is_completed', false)
                    ->setParameter('today', $today, \Doctrine\DBAL\Types\Type::DATETIME)
                    ->setParameter('tomorrow', $tomorrow, \Doctrine\DBAL\Types\Type::DATETIME)
                    ;
                $query = $qb->getQuery();
                return $query->getSingleScalarResult();

	}

}

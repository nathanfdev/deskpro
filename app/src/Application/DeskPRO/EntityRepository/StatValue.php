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
 */

namespace Application\DeskPRO\EntityRepository;

use Doctrine\ORM\EntityRepository;
use Application\DeskPRO\App;

class StatValue extends AbstractEntityRepository
{
	/**
	 * Get the StatValues for a Stat
	 *
	 * @param int $stat_id The Stat id
	 * @param int $limit The limit
	 * @return ArrayCollection
	 */
	public function getForStat($stat_id, $limit = null)
	{
		$qb = $this->getForStatQuery();

		if (false === is_null($limit)) {
			$qb->setMaxResults($limit);
		}

		return $qb
		       ->getQuery()
		       ->execute();
	}

	/**
	 * Get the StatValues for a period. We work backwards from $end_date
	 * for $limit number
	 *
	 * @param int $stat_id The Stat id
	 * @param \DateTime $end_date The end date
	 * @param int $limit The number of results to retrieve (optional)
	 * @return array
	 */
	public function getForStatEndDateLimit($stat_id, \DateTime $end_date, $limit = null)
	{
		$qb = $this->getForStatBuilder($stat_id)
			    ->andWhere("sv.stat_unix < :stat_unix")
			    ->setParameter('stat_unix', $end_date->format('U'));

		if (false === is_null($limit)) {
			$qb->setMaxResults($limit);
		}

		return $qb->orderBy('sv.stat_unix', 'DESC')
			  ->getQuery()
			  ->getArrayResult();
	}

	/**
	 * Get the StatValues for a period (between $start_date and $end_date)
	 *
	 * @param int $stat_id The Stat id
	 * @param \DateTime $start_date The start date
	 * @param \DateTime $end_date The end date
	 * @return array
	 */
	public function getForStatRangeDate($stat_id, \DateTime $start_date, \DateTime $end_date)
	{
		return $this->getForStatBuilder($stat_id)
			    ->andWhere("sv.stat_unix >= :start_unix")
			    ->andWhere("sv.stat_unix <= :end_unix")
			    ->setParameter('start_unix', $start_date->format('U'))
			    ->setParameter('end_unix', $end_date->format('U'))
			    ->orderBy('sv.stat_unix', 'DESC')
			    ->getQuery()
			    ->getArrayResult();
	}

	/**
	 * Get that StatValue for a hour
	 *
	 * @param int $stat_id The Stat id
	 * @param \DateTime $date The date
	 * @return StatValue
	 */
	public function getForStatByHour($stat_id, \DateTime $date)
	{
		$day_start = mktime($date->format('H'), 0, 0, $date->format('n'), $date->format('j'), $date->format('Y'));
		$day_end   = mktime($date->format('H'), 59, 59, $date->format('n'), $date->format('j'), $date->format('Y'));

		try {
			$stat_value =
					$this->getForStatBuilder($stat_id)
							->andWhere("sv.stat_unix BETWEEN :day_start AND :day_end")
							->setParameter('day_start', $day_start)
							->setParameter('day_end', $day_end)
							->getQuery()
							->getSingleResult();
		} catch (\Doctrine\Orm\NoResultException $e) {
			$stat_value = null;
		}

		return $stat_value;
	}

	/**
	 * Get that StatValue for a day
	 *
	 * @param int $stat_id The Stat id
	 * @param \DateTime $date The date
	 * @return StatValue
	 */
	public function getForStatByDay($stat_id, \DateTime $date)
	{
		$day_start = mktime(0, 0, 0, $date->format('n'), $date->format('j'), $date->format('Y'));
		$day_end   = mktime(23, 59, 59, $date->format('n'), $date->format('j'), $date->format('Y'));

		try {
			$stat_value =
				$this->getForStatBuilder($stat_id)
				     ->andWhere("sv.stat_unix BETWEEN :day_start AND :day_end")
				     ->setParameter('day_start', $day_start)
				     ->setParameter('day_end', $day_end)
				     ->getQuery()
				     ->getSingleResult();
		} catch (\Doctrine\Orm\NoResultException $e) {
			$stat_value = null;
		}

		return $stat_value;
	}

	/**
	 * Get that StatValue for a month
	 *
	 * @param int $stat_id The Stat id
	 * @param \DateTime $date The date
	 * @return StatValue
	 */
	public function getForStatByMonth($stat_id, \DateTime $date)
	{
		$month_start = \Orb\Util\Dates::firstDayInMonth($date->format('n'), $date->format('Y'));
		$month_end   = \Orb\Util\Dates::lastDayInMonth($date->format('n'), $date->format('Y'));

		try {
			$stat_value =
				$this->getForStatBuilder($stat_id)
				     ->andWhere("sv.stat_unix BETWEEN :month_start AND :month_end")
				     ->setParameter('month_start', $month_start)
				     ->setParameter('month_end', $month_end)
				     ->getQuery()
				     ->getSingleResult();
		} catch (\Doctrine\Orm\NoResultException $e) {
			$stat_value = null;
		}

		return $stat_value;
	}

	/**
	 * Get that StatValue for a year
	 *
	 * @param int $stat_id The Stat id
	 * @param \DateTime $date The date
	 * @return StatValue
	 */
	public function getForStatByYear($stat_id, \DateTime $date)
	{
		$year_start = mktime(0, 0, 0, 1, 1, $date->format('Y'));
		$year_end   = mktime(23, 59, 59, 12, 31, $date->format('Y'));

		try {
			$stat_value =
				$this->getForStatBuilder($stat_id)
				     ->andWhere("sv.stat_unix BETWEEN :year_start AND :year_end")
				     ->setParameter('year_start', $year_start)
				     ->setParameter('year_end', $year_end)
				     ->getQuery()
				     ->getSingleResult();
		} catch (\Doctrine\Orm\NoResultException $e) {
			$stat_value = null;
		}

		return $stat_value;
	}

	/**
	 * Get a basic StatValue query
	 *
	 * @param int $stat_id The Stat Id
	 * @return QueryBuilder The QueryBuilder Object
	 */
	protected function getForStatBuilder($stat_id)
	{
		return $this->getEntityManager()->createQueryBuilder()
			->select('sv')
			->from('DeskPRO:StatValue', 'sv')
			->innerJoin('sv.stat', 's')
			->where('s.id = :stat_id')
			->setParameter('stat_id', $stat_id);
	}
}

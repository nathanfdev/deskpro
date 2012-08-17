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

class StatValueGroup extends AbstractEntityRepository
{
	/**
	 * Get the references Id's for a StatValue
	 *
	 * @param array $stat_value_ids List of StatValue Ids
	 * @return array()
	 */
	public function getReferenceIdsByStatValues($stat_value_ids)
	{
		if (0 === count($stat_value_ids)) {
			return array();
		}

		$query = "SELECT DISTINCT grouping_ref
			FROM stat_value_group svg
			WHERE svg.stat_value_id IN (" . join(',', $stat_value_ids) . ")";

		$references = App::getDb()->fetchAllCol($query);

		$referenceIds = array();
		foreach ($references as $reference) {
			if (false === is_null($reference)) {
				$referenceIds[] = $reference;
			}
		}

		return $referenceIds;
	}

	/**
	 * Get the StatValueGroups for a StatValue
	 *
	 * @param int $stat_value_id The StatValue id
	 *
	 * @return array
	 */
	public function getForStatValue($stat_value_id)
	{
		$qb = $this->getEntityManager()->createQueryBuilder()
			    ->select('svg')
			    ->from('DeskPRO:StatValueGroup', 'svg')
			    ->innerJoin('svg.stat_value', 'sv')
			    ->where('sv.id = :stat_value_id')
			    ->setParameter('stat_value_id', $stat_value_id);

		return $qb->orderBy('svg.stat_unix', 'DESC')
			  ->getQuery()
			  ->getArrayResult();
	}

	/**
	 * Get the StatValueGroup for a hour
	 *
	 * @param int $stat_value_id The StatValue id
	 * @param string $grouping_ref The grouping ref
	 * @param \DateTime $date The date
	 * @return StatValueGroup
	 */
	public function getForStatValueByHour($stat_value_id, $grouping_ref, \DateTime $date)
	{
		$day_start = mktime($date->format('H'), 0, 0, $date->format('n'), $date->format('j'), $date->format('Y'));
		$day_end   = mktime($date->format('H'), 59, 59, $date->format('n'), $date->format('j'), $date->format('Y'));

		try {
			$stat_value_group = $this->getForStatValueBuilder($stat_value_id, $grouping_ref)
					->andWhere("svg.stat_unix BETWEEN :day_start AND :day_end")
					->setParameter('day_start', $day_start)
					->setParameter('day_end', $day_end)
					->getQuery()
					->getSingleResult();
		} catch (\Doctrine\Orm\NoResultException $e) {
			$stat_value_group = null;
		}

		return $stat_value_group;
	}

	/**
	 * Get the StatValueGroup for a day
	 *
	 * @param int $stat_value_id The StatValue id
	 * @param string $grouping_ref The grouping ref
	 * @param \DateTime $date The date
	 * @return StatValueGroup
	 */
	public function getForStatValueByDay($stat_value_id, $grouping_ref, \DateTime $date)
	{
		$day_start = mktime(0, 0, 0, $date->format('n'), $date->format('j'), $date->format('Y'));
		$day_end   = mktime(23, 59, 59, $date->format('n'), $date->format('j'), $date->format('Y'));

		try {
			$stat_value_group = $this->getForStatValueBuilder($stat_value_id, $grouping_ref)
				->andWhere("svg.stat_unix BETWEEN :day_start AND :day_end")
				->setParameter('day_start', $day_start)
				->setParameter('day_end', $day_end)
				->getQuery()
				->getSingleResult();
		} catch (\Doctrine\Orm\NoResultException $e) {
			$stat_value_group = null;
		}

		return $stat_value_group;
	}

	/**
	 * Get the StatValueGroup for a month
	 *
	 * @param int $stat_value_id The StatValue id
	 * @param string $grouping_ref The grouping ref
	 * @param \DateTime $date The date
	 * @return StatValueGroup
	 */
	public function getForStatValueByMonth($stat_value_id, $grouping_ref, \DateTime $date)
	{
		$month_start = \Orb\Util\Dates::firstDayInMonth($date->format('n'), $date->format('Y'));
		$month_end   = \Orb\Util\Dates::lastDayInMonth($date->format('n'), $date->format('Y'));

		try {
			$stat_value_group = $this->getForStatValueBuilder($stat_value_id, $grouping_ref)
				->andWhere("svg.stat_unix BETWEEN :month_start AND :month_end")
				->setParameter('month_start', $month_start)
				->setParameter('month_end', $month_end)
				->getQuery()
				->getSingleResult();
		} catch (\Doctrine\Orm\NoResultException $e) {
			$stat_value_group = null;
		}

		return $stat_value_group;
	}

	/**
	 * Get the StatValueGroup for a year
	 *
	 * @param int $stat_value_id The StatValue id
	 * @param string $grouping_ref The grouping ref
	 * @param \DateTime $date The date
	 * @return StatValueGroup
	 */
	public function getForStatValueByYear($stat_value_id, $grouping_ref, \DateTime $date)
	{
		$year_start = mktime(0, 0, 0, 1, 1, $date->format('Y'));
		$year_end   = mktime(23, 59, 59, 12, 31, $date->format('Y'));

		try {
			$stat_value_group = $this->getForStatValueBuilder($stat_value_id, $grouping_ref)
				->andWhere("svg.stat_unix BETWEEN :year_start AND :year_end")
				->setParameter('year_start', $year_start)
				->setParameter('year_end', $year_end)
				->getQuery()
				->getSingleResult();
		} catch (\Doctrine\Orm\NoResultException $e) {
			$stat_value_group = null;
		}

		return $stat_value_group;
	}

	/**
	 * Get a basic StatValueGroup query
	 *
	 * @param int $stat_value_id The StatValue Id
	 * @param string $grouping_ref The grouping ref
	 * @return QueryBuilder The QueryBuilder Object
	 */
	protected function getForStatValueBuilder($stat_value_id, $grouping_ref)
	{
		$qb = $this->getEntityManager()->createQueryBuilder()
			->select('svg')
			->from('DeskPRO:StatValueGroup', 'svg')
			->innerJoin('svg.stat_value', 'sv')
			->where('sv.id = :stat_value_id')
			->setParameter('stat_value_id', $stat_value_id);

		if (true === is_null($grouping_ref)) {
			$qb->andWhere('svg.grouping_ref IS NULL');
		}
		else {
			$qb->andWhere('svg.grouping_ref = :grouping_ref')
			   ->setParameter('grouping_ref', $grouping_ref);
		}

		return $qb;
	}
}

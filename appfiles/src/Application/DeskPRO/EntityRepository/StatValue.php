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

use Doctrine\ORM\EntityRepository;
use Application\DeskPRO\App;

class StatValue extends EntityRepository
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
	 * Get that StatValue for a day
	 *
	 * @param int $stat_id The Stat id
	 * @param \DateTime $date The date
	 * @return StatValue
	 */
	public function getForStatByDay($stat_id, \DateTime $date)
	{
		$stat_value = $this->getForStatQuery($stat_id)
			->andWhere("FROM_UNIXTIME(sv.stat_unix, '%Y-%m-%d') = ?", $date->format('Y-m-d'))
			->getQuery()
			->getSingleResult();

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
		$stat_value = $this->getForStatQuery($stat_id)
			->andWhere("FROM_UNIXTIME(sv.stat_unix, '%Y-%m') = ?", $date->format('Y-m'))
			->getQuery()
			->getSingleResult();

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
		$stat_value = $this->getForStatQuery($stat_id)
			->andWhere("FROM_UNIXTIME(sv.stat_unix, '%Y') = ?", $date->format('Y'))
			->getQuery()
			->getSingleResult();

		return $stat_value;
	}

	/**
	 * Get a basic StatValue query
	 *
	 * @param int $stat_id The Stat Id
	 * @return QueryBuilder The QueryBuilder Object
	 */
	protected function getForStatQuery($stat_id)
	{
		return $this->getEntityManager()->createQueryBuilder()
			->select('sv')
			->from('DeskPRO:StatValue', 'sv')
			->where('sv.stat_id = ?', $stat_id);
	}
}
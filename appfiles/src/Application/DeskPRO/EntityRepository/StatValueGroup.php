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

class StatValueGroup extends EntityRepository
{
	public function getReferenceIdsByStat($stat_id)
	{
		$references = App::getDb()->fetchAllCol("
			SELECT grouping_id
			FROM stat_value_group svg
			INNER JOIN stat_value sv ON sv.id = svg.stat_value_id
			WHERE sv.stat_id = $stat_id
		");

		$referenceIds = array();
		foreach ($references as $reference) {
			if (false === is_null($reference['grouping_id'])) {
				$referenceIds[] = $reference['grouping_id'];
			}
		}

		return $referenceIds;
	}

	/**
	 * Get that StatValueGroup for a day
	 *
	 * @param int $stat_value_id The StatValue id
	 * @param int $grouping_id The grouping id
	 * @param \DateTime $date The date
	 * @return StatValueGroup
	 */
	public function getForStatValueByDay($stat_value_id, $grouping_id, \DateTime $date)
	{
		$stat_value_group = $this->getForStatValueQuery($stat_value_id, $grouping_id)
			->andWhere("FROM_UNIXTIME(svg.stat_unix, '%Y-%m-%d') = ?", $date->format('Y-m-d'))
			->getQuery()
			->getSingleResult();

		return $stat_value_group;
	}

	/**
	 * Get that StatValueGroup for a month
	 *
	 * @param int $stat_value_id The StatValue id
	 * @param int $grouping_id The grouping id
	 * @param \DateTime $date The date
	 * @return StatValueGroup
	 */
	public function getForStatValueByMonth($stat_value_id, $grouping_id, \DateTime $date)
	{
		$stat_value_group = $this->getForStatValueQuery($stat_value_id, $grouping_id)
			->andWhere("FROM_UNIXTIME(svg.stat_unix, '%Y-%m') = ?", $date->format('Y-m'))
			->getQuery()
			->getSingleResult();

		return $stat_value_group;
	}

	/**
	 * Get that StatValueGroup for a year
	 *
	 * @param int $stat_value_id The StatValue id
	 * @param int $grouping_id The grouping id
	 * @param \DateTime $date The date
	 * @return StatValueGroup
	 */
	public function getForStatValueByYear($stat_value_id, $grouping_id, \DateTime $date)
	{
		$stat_value_group = $this->getForStatValueQuery($stat_value_id, $grouping_id)
			->andWhere("FROM_UNIXTIME(svg.stat_unix, '%Y') = ?", $date->format('Y'))
			->getQuery()
			->getSingleResult();

		return $stat_value_group;
	}

	/**
	 * Get a basic StatValueGroup query
	 *
	 * @param int $stat_value_id The StatValue Id
	 * @param int $grouping_id The grouping id
	 * @return QueryBuilder The QueryBuilder Object
	 */
	protected function getForStatValueQuery($stat_value_id, $grouping_id)
	{
		return $this->getEntityManager()->createQueryBuilder()
			->select('svg')
			->from('DeskPRO:StatValueGroup', 'svg')
			->where('svg.stat_value_id = ?', $stat_value_id)
			->andWhere('svg.grouping_id = ?', $grouping_id);
	}
}
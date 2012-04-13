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

class Stat extends EntityRepository
{

	/**
	 * Get enabled stats
	 *
	 * @return array
	 */
	public function getEnabledStats()
	{
		$stats = $this->getEntityManager()->createQuery("
			SELECT s
			FROM DeskPRO:Stat s
			WHERE s.disabled = :disabled
			ORDER BY s.title ASC
		")->setParameter('disabled', false)->execute();

		return $stats;
	}

	/**
	 * Get the daily stat ids requiring update
	 *
	 * @param \DateTime $date The DateTime to check against [defaults to today]
	 * @return array
	 */
	public function getDailyStatIdsRequiringUpdate(\DateTime $date = null)
	{
		if (is_null($date)) {
			// Get today
			$date = new \DateTime();
		}

		$db    = App::getDb();
		$query = $this->getAllIdsRequiringUpdateQuery('daily');
		$query .= " AND DATE_FORMAT(s.last_run, '%Y-%m-%d') < '" . $date->format('Y-m-d') . "'";

		return $db->fetchAllCol($query);
	}

	/**
	 * Get the monthly stat ids requiring update
	 *
	 * @param \DateTime $date The DateTime to check against [defaults to today]
	 * @return array
	 */
	public function getMonthlyStatIdsRequiringUpdate(\DateTime $date = null)
	{
		if (is_null($date)) {
			// Get today
			$date = new \DateTime();
		}

		$db    = App::getDb();
		$query = $this->getAllIdsRequiringUpdateQuery('monthly');
		$query .= " AND DATE_FORMAT(s.last_run, '%Y-%m') < '" . $date->format('Y-m') . "'";

		return $db->fetchAllCol($query);
	}

	/**
	 * Get the yearly stat ids requiring update
	 *
	 * @param \DateTime $date The DateTime to check against [defaults to today]
	 * @return array
	 */
	public function getYearlyStatIdsRequiringUpdate(\DateTime $date = null)
	{
		if (is_null($date)) {
			// Get today
			$date = new \DateTime();
		}

		$db    = App::getDb();
		$query = $this->getAllIdsRequiringUpdateQuery('yearly');
		$query .= " AND DATE_FORMAT(s.last_run, '%Y') < '" . $date->format('Y') . "'";

		return $db->fetchAllCol($query);
	}

	/**
	 * Get Stats by id
	 *
	 * @param array $stat_ids The Stat ids
	 * @return ArrayCollection
	 */
	public function getByIds($stat_ids)
	{
		if (0 === count($stat_ids)) {
			return array();
		}

		$qb = $this->getEntityManager()->createQueryBuilder();

		return $qb->select('s')
			  ->from('DeskPRO:Stat', 's')
			  ->add('where', $qb->expr()->in('s.id', ':stat_ids'))
			  ->setParameter('stat_ids', $stat_ids)
			  ->getQuery()
			  ->getResult();
	}

	/**
	 * Get the stats ids requiring updating query
	 *
	 * @param string $run_frequency The run frequency to check against
	 * @return string
	 */
	protected function getAllIdsRequiringUpdateQuery($run_frequency)
	{
		if (false === \Application\DeskPRO\Entity\Stat::isValidRunFrequency($run_frequency)) {
			throw new \Exception("Invalid run_frequency $run_frequency.");
		}

		return "SELECT id
			FROM stat s
			WHERE s.generate_stats = 1
			AND s.run_frequency = '$run_frequency'
			OR s.last_run IS NULL";
	}
}

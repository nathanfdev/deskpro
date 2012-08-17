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

class ReportDashboardStat extends AbstractEntityRepository
{
	
	/**
	 * Get dashboards stats
	 *
	 * @return array
	 */
	public function getDashboardStats($dashboard_id)
	{
		$dashboards = $this->getEntityManager()->createQuery("
			SELECT rds
			FROM DeskPRO:ReportDashboardStat rds
			WHERE rds.report_dashboard = :dashboard_id
			ORDER BY rds.slot_number
		")->setParameter('dashboard_id', $dashboard_id)->execute();

		return $dashboards;
	}
	
	/**
	 * Calculate the next dashboard slot number
	 */
	public function getNextDashboardStatSlot($dashboard_id)
	{
		return $this->getLastDashboardStatSlot($dashboard_id) + 1;
	}
	
	/**
	 * Get the last dashboard slot number
	 */
	public function getLastDashboardStatSlot($dashboard_id)
	{
		try {
			$last_slot = $this->getEntityManager()->createQuery("
				SELECT rds.slot_number
				FROM DeskPRO:ReportDashboardStat rds
				WHERE rds.report_dashboard = :dashboard_id
				ORDER BY rds.slot_number DESC
			")->setParameter('dashboard_id', $dashboard_id)
			->setMaxResults(1)
			->getSingleScalarResult();
		}
		catch (\Doctrine\ORM\NoResultException $exception) {
			$last_slot = 0;
		}
		
		return $last_slot;
	}
}

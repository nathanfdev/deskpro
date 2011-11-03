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

class ReportDashboardStat extends EntityRepository
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
}
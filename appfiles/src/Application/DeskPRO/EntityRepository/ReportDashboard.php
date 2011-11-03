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

class ReportDashboard extends EntityRepository
{
	
	/**
	 * Get dashboards
	 *
	 * @return array
	 */
	public function getDashboards()
	{
		$dashboards = $this->getEntityManager()->createQuery("
			SELECT ds
			FROM DeskPRO:ReportDashboard ds
		")->execute();

		return $dashboards;
	}
}
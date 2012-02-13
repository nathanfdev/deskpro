<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage AdminBundle
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\ReportBundle\Controller;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity\ReportDashboardStat;
use Application\ReportBundle\Form\EditReportDashboardStatType;

class DashboardController extends AbstractController
{
	/**
	 * Edit a dashboard stat
	 */
	public function editAction($dashboard_stat_id)
	{
		$dashboardStat = $this->getDashboard($dashboard_stat_id);
		$stat	       = $dashboardStat->getStat();
		
		$form = $this->get('form.factory')->create(new EditReportDashboardStatType(), $dashboardStat);
		
		if ($this->in->getBool('process')) {
			$form->bindRequest($this->get('request'));
			
			if ($form->isValid()) {
				App::getOrm()->persist($dashboardStat);
				App::getOrm()->flush();
			}
		}
		
		return $this->render('ReportBundle:DashboardStat:edit.html.twig', array(
			'dashboard_stat' => $dashboardStat,
			'stat'		 => $stat,
			'form'      	 => $form->createView(),
		));
	}
	
	/**
	 * Get the Dashboard Stat Entity
	 *
	 * @throws NotFoundHttpException
	 */
	protected function getDashboardStat($dashboard_stat_id)
	{
		$dashboardStat = App::getEntityRepository('DeskPRO:ReportDashboardStat')->find($dashboard_stat_id);
		if (!$dashboardStat) {
			throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException("error_404_dashboard_stat");
		}
		
		return $dashboardStat;
	}
	
}

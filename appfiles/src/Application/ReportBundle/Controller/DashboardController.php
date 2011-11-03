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
use Application\DeskPRO\Entity\ReportDashboard;
use Application\DeskPRO\Entity\ReportDashboardStat;
use Application\ReportBundle\Form\EditReportDashboardType;

class DashboardController extends AbstractController
{
	
	public function indexAction()
	{		
		return $this->render('ReportBundle:Dashboard:index.html.twig');
	}
	
	/**
	 * View the dashboard
	 */
	public function viewAction($dashboard_id)
	{
		$dashboard       = $this->getDashboard($dashboard_id);
		$dashboard_stats = App::getEntityRepository('DeskPRO:ReportDashboardStat')->getDashboardStats($dashboard_id);
		
		$all_stats	 = App::getEntityRepository('DeskPRO:Stat')->getEnabledStats();
		
		return $this->render('ReportBundle:Dashboard:view.html.twig', array(
			'dashboard' 		=> $dashboard,
			'dashboard_stats'	=> $dashboard_stats,
			'all_stats'		=> $all_stats
		));
	}
	
	/**
	 * Create/Edit a dashboard
	 */
	public function editAction($dashboard_id)
	{
		if (!$dashboard_id) {
			$dashboard = new ReportDashboard();
		} else {
			$dashboard = $this->getDashboard($dashboard_id);
		}

		$form = $this->get('form.factory')->create(new EditReportDashboardType(), $dashboard);

		if ($this->in->getBool('process')) {
			$form->bindRequest($this->get('request'));

			if ($form->isValid()) {
				App::getOrm()->persist($dashboard);
				App::getOrm()->flush();

				$this->session->setFlash('saved', $dashboard->title);
				return $this->redirectRoute('trend_dashboard_view', array(
					'dashboard_id'	=> $dashboard->id
				));
			}
		}

		return $this->render('ReportBundle:Dashboard:edit.html.twig', array(
			'dashboard' => $dashboard,
			'form'      => $form->createView(),
		));
	}
	
	/**
	 * Add a stat to the dashboard
	 */
	public function addStatAction($dashboard_id, $stat_id)
	{
		$dashboard = $this->getDashboard($dashboard_id);
		$stat      = $this->getStat($stat_id);
		
		$next_slot_number = App::getEntityRepository('DeskPRO:ReportDashboardStat')
				       ->getNextDashboardStatSlot($dashboard_id);
		
		$dashboardStat = new ReportDashboardStat();
		$dashboardStat->setReportDashboard($dashboard);
		$dashboardStat->setStat($stat);
		$dashboardStat->setSlotNumber($next_slot_number);
		
		App::getOrm()->persist($dashboardStat);
		App::getOrm()->flush();
		
		return $this->redirectRoute('trend_dashboard_view', array(
			'dashboard_id'	=> $dashboard->id
		));
	}
	
	/**
	 * Remove a stat from the dashboard
	 */
	public function removeStatAction($dashboard_id, $dashboard_stat_id)
	{
		$dashboard     = $this->getDashboard($dashboard_id);
		$dashboardStat = $this->getDashboardStat($dashboard_stat_id);
		
		App::getOrm()->remove($dashboardStat);
		App::getOrm()->flush();
		
		return $this->redirectRoute('trend_dashboard_view', array(
			'dashboard_id'	=> $dashboard->id	
		));
	}
	
	/**
	 * Get the Dashboard Entity
	 *
	 * @throws NotFoundHttpException
	 */
	protected function getDashboard($dashboard_id)
	{
		$dashboard = App::getEntityRepository('DeskPRO:ReportDashboard')->find($dashboard_id);
		if (!$dashboard) {
			throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException("error_404_stat_dashboard");
		}
		
		return $dashboard;
	}
	
	/**
	 * Get the Stat Entity
	 *
	 * @throws NotFoundHttpException
	 */
	protected function getStat($stat_id)
	{
		$stat = App::getEntityRepository('DeskPRO:ReportDashboard')->find($stat_id);
		if (!$stat) {
			throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException("error_404_stat");
		}
		
		return $stat;
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

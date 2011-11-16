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
use Application\ReportBundle\Form\EditReportDashboardStatType;

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
				return $this->redirectRoute('report_trend_dashboard_view', array(
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
	 * Saves the dashboard state
	 */
	public function saveDashboardStateAction($dashboard_id) {

		$request = $this->getRequest();

		$success         = true;
		$dashboard       = $this->getDashboard($dashboard_id);
		$dashboard_state = json_decode($request->get('dashboard_state'), true);

		// Save dashboard information
		$dashboard->setNumberColumns($dashboard_state['number_columns']);
		App::getOrm()->persist($dashboard);

		// Save the widgets
		foreach ($dashboard_state['widgets'] as $widget) {
			// Get the widget and update it
			try {
				$dashboardStat = $this->getDashboardStat($widget['id']);

				$dashboardStat->setGridSlots($widget['number_columns']);
				$dashboardStat->setSlotNumber($widget['slot_number']);

				App::getOrm()->persist($dashboardStat);
			}
			catch (\Exception $e) {
				$success = false;
			}
		}
		App::getOrm()->flush();

		return $this->createJsonResponse(array('success' => $success));
	}

	/**
	 * Remove a widget from the dashboard
	 */
	public function ajaxDeleteWidgetAction($dashboard_id, $dashboard_stat_id)
	{
		$success = true;

		try {
			$dashboard     = $this->getDashboard($dashboard_id);
			$dashboardStat = $this->getDashboardStat($dashboard_stat_id);

			App::getOrm()->remove($dashboardStat);
			App::getOrm()->flush();
		}
		catch (\Exception $e) {
			$success = false;
		}

		return $this->createJsonResponse(array('success' => $success));
	}

	/**
	 * Create and fetch dashboard widget
	 */
	public function ajaxCreateWidgetAction($dashboard_id, $stat_id)
	{
		$dashboard     = $this->getDashboard($dashboard_id);
		$stat      	   = $this->getStat($stat_id);
		$dashboardStat = $this->getDashboard($dashboard_id);

		$dashboardStat = new ReportDashboardStat();
		$form = $this->get('form.factory')->create(new EditReportDashboardStatType(), $dashboardStat);

		if ($this->in->getBool('process')) {
			$form->bindRequest($this->get('request'));

			if ($form->isValid()) {
				$next_slot_number = App::getEntityRepository('DeskPRO:ReportDashboardStat')
				       ->getNextDashboardStatSlot($dashboard_id);

				$dashboard_stat = new ReportDashboardStat();
				$dashboard_stat->setReportDashboard($dashboard);
				$dashboard_stat->setStat($stat);
				$dashboard_stat->setSlotNumber($next_slot_number);

				App::getOrm()->persist($dashboard_stat);
				App::getOrm()->flush();

				$widget = $this->getWidgetDetails($dashboard_stat);

				return $this->createJsonResponse(array('widget' => $widget));
			}
		}

		$html = $this->renderView('ReportBundle:Dashboard:editWidget.html.twig', array(
			'dashboard' => $dashboard,
			'stat'		=> $stat,
			'form'      => $form->createView(),
		));

		return $this->createJsonResponse(array('html' => $html));
	}

	/**
	 * Edit dashboard widget
	 */
	public function ajaxEditWidgetAction($dashboard_id, $dashboard_stat_id)
	{
		$dashboard          = $this->getDashboard($dashboard_id);
		$dashboard_stat     = $this->getDashboardStat($dashboard_stat_id);

		$form = $this->get('form.factory')->create(new EditReportDashboardStatType(), $dashboard_stat);

		if ($this->in->getBool('process')) {
			$form->bindRequest($this->get('request'));

			if ($form->isValid()) {
				$next_slot_number = App::getEntityRepository('DeskPRO:ReportDashboardStat')
				       ->getNextDashboardStatSlot($dashboard_id);

				App::getOrm()->persist($dashboard_stat);
				App::getOrm()->flush();

				$widget = $this->getWidgetDetails($dashboard_stat);

				return $this->createJsonResponse(array('widget' => $widget));
			}
		}

		$html = $this->renderView('ReportBundle:Dashboard:editWidget.html.twig', array(
			'dashboard' => $dashboard,
			'form'      => $form->createView(),
		));

		return $this->createJsonResponse(array('html' => $html));
	}

	/**
	 * Get the Dashboard Widgets
	 */
	public function ajaxFetchWidgetsAction($dashboard_id)
	{
		$dashboard       = $this->getDashboard($dashboard_id);
		$dashboard_stats = App::getEntityRepository('DeskPRO:ReportDashboardStat')->getDashboardStats($dashboard_id);

		$widgets = array();
		foreach ($dashboard_stats as $dashboard_stat) {
			$widgets[] = $this->getWidgetDetails($dashboard_stat);
		}

		return $this->createJsonResponse(array('widgets' => $widgets));
	}

	/**
	 * Get the widget deatails ready for JSON response
	 *
	 * @param Application\DeskPRO\Entity\DashboardStat $dashboard_stat
	 * @return array Details ready for JSON response
	 */
	protected function getWidgetDetails($dashboard_stat)
	{
		$stat = $dashboard_stat->getStat();

		$view_class = $dashboard_stat->getViewClass();
		$chart = new $view_class($dashboard_stat->getStat());

		return array(
			'id' 		=> $dashboard_stat->getId(),
			'chart_vendor'	=> $chart->getViewChartVendor(),
			'chart_class'   => $chart->getViewChartClass(),
			'grid_slots'	=> $dashboard_stat->getGridSlots(),
			'slot_number'   => $dashboard_stat->getSlotNumber(),
			'stat'		=> array(
				'id'	=> $stat->getId(),
				'title' => $dashboard_stat->getTitle(),
			),
		);
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
		$stat = App::getEntityRepository('DeskPRO:Stat')->find($stat_id);
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

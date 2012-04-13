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
 * @subpackage AdminBundle
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
		$first = \Orb\Util\Arrays::getFirstItem($this->dashboards);
		if (!$first) {
			return $this->redirectRoute('report_trend_dashboard_new');
		}

		return $this->redirectRoute('report_trend_dashboard_view', array('dashboard_id' => $first->getId()));
	}

	/**
	 * View the dashboard
	 */
	public function viewAction($dashboard_id)
	{
		$dashboard       = $this->getDashboard($dashboard_id);
		$dashboard_stats = App::getEntityRepository('DeskPRO:ReportDashboardStat')->getDashboardStats($dashboard_id);

		$all_stats	 = App::getEntityRepository('DeskPRO:Stat')->getEnabledStats();

		$form = $this->get('form.factory')->create(new EditReportDashboardType(), $dashboard);

		return $this->render('ReportBundle:Dashboard:view.html.twig', array(
			'dashboard' 		=> $dashboard,
			'dashboard_stats'	=> $dashboard_stats,
			'all_stats'		=> $all_stats,
			'form'      		=> $form->createView(),
		));
	}

	/**
	 * Create a dashboard
	 */
	public function newAction()
	{
		$dashboard = new ReportDashboard();

		$form = $this->get('form.factory')->create(new EditReportDashboardType(), $dashboard);

		if ($this->in->getBool('process')) {
			$request = $this->getRequest();
			$form->bindRequest($request);

			$order = App::getDb()->fetchColumn("SELECT display_order FROM report_dashboard ORDER BY display_order DESC LIMIT 1");
			if (!$order) $order = 10;
			$order += 10;

			$dashboard->display_order = $order;

			App::getOrm()->persist($dashboard);
			App::getOrm()->flush();
			return $this->redirectRoute('report_trend_dashboard_view', array(
				'dashboard_id' => $dashboard->getId(),
			));
		}

		return $this->render('ReportBundle:Dashboard:edit.html.twig', array(
			'dashboard' => $dashboard,
			'form'      => $form->createView(),
		));
	}

	/**
	 * Edit a dashboard
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
			$request = $this->getRequest();
			$form->bindRequest($request);

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
					$dashboardStat->setGridColumns($widget['grid_columns']);
					$dashboardStat->setGridRows($widget['grid_rows']);
					$dashboardStat->setSlotNumber($widget['slot_number']);

					App::getOrm()->persist($dashboardStat);
				}
				catch (\Exception $e) {
					$success = false;
				}
			}

			App::getOrm()->flush();
			return $this->createJsonResponse(array('success' => true));
		}

		return $this->render('ReportBundle:Dashboard:edit.html.twig', array(
			'dashboard' => $dashboard,
			'form'      => $form->createView(),
		));
	}

	public function ajaxUpdateOrdersAction()
	{
		$ids = $this->in->getCleanValueArray('dashboard_ids', 'uint', 'discard');

		$this->db->beginTransaction();
		try {
			$order = 10;
			foreach ($ids as $id) {
				$this->db->update('report_dashboard', array('display_order' => $order), array('id' => $id));
				$order += 10;
			}

			$this->db->commit();
		} catch (\Exception $e) {
			$this->db->rollback();
			throw $e;
		}

		return $this->createJsonResponse(array('success' => true));
	}

	/**
	 * Remove a dashboard
	 */
	public function deleteAction($dashboard_id)
	{
		try {
			$dashboard     = $this->getDashboard($dashboard_id);

			App::getOrm()->remove($dashboard);
			App::getOrm()->flush();
		}
		catch (\Exception $e) {
			die($e);
		}

		return $this->redirectRoute('report_trend_index');
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
	 * Add a new stat to the dashboard
	 */
	public function dashboardStatNewAction($dashboard_id, $stat_id)
	{
		$dashboard     = $this->getDashboard($dashboard_id);
		$stat          = $this->getStat($stat_id);

		$dashboardStat = new ReportDashboardStat();
		$dashboardStat->setReportDashboard($dashboard);
		$dashboardStat->setStat($stat);
		$dashboardStat->setTitle($stat->getTitle());
		$dashboardStat->setNumberDataPoints($stat->getDefaultDataPointCount());
		$dashboardStat->setDisplayGrouping(false);

		$form = $this->get('form.factory')->create(new EditReportDashboardStatType(), $dashboardStat);

		if ($this->in->getBool('process')) {
			$form->bindRequest($this->get('request'));

			if ($form->isValid()) {
				$next_slot_number = App::getEntityRepository('DeskPRO:ReportDashboardStat')
				       ->getNextDashboardStatSlot($dashboard_id);

				$dashboardStat->setSlotNumber($next_slot_number);

				App::getOrm()->persist($dashboardStat);
				App::getOrm()->flush();

				$widget = $this->getWidgetDetails($dashboardStat);

				return $this->createJsonResponse(array('widget' => $widget));
			}
		}

		$form_route = $this->generateUrl('report_trend_dashboard_stat_new', array(
			'dashboard_id' => $dashboard->getId(),
			'stat_id' => $stat->getId(),
		));

		$html = $this->renderView('ReportBundle:Dashboard:editWidget.html.twig', array(
			'dashboard'  => $dashboard,
			'stat'       => $stat,
			'form'       => $form->createView(),
			'form_route' => $form_route,
			'form_id'    => 'dashboard_widget_new_form',
		));

		return $this->createJsonResponse(array('html' => $html));
	}


	/**
	 * Edit a dashboard stat
	 */
	public function dashboardStatEditAction($dashboard_id, $dashboard_stat_id)
	{
		$dashboardStat     = $this->getDashboardStat($dashboard_stat_id);
		$dashboard = $dashboardStat->getReportDashboard();
		$stat      = $dashboardStat->getStat();

		$form = $this->get('form.factory')->create(new EditReportDashboardStatType(), $dashboardStat);

		if ($this->in->getBool('process')) {
			$form->bindRequest($this->get('request'));

			if ($form->isValid()) {
				App::getOrm()->persist($dashboardStat);
				App::getOrm()->flush();

				$widget = $this->getWidgetDetails($dashboardStat);

				return $this->createJsonResponse(array('widget' => $widget));
			}
		}

		$form_route = $this->generateUrl('report_trend_dashboard_stat_edit', array(
			'dashboard_id' => $dashboard->getId(),
			'dashboard_stat_id' => $dashboardStat->getId(),
		));

		$html = $this->renderView('ReportBundle:Dashboard:editWidget.html.twig', array(
			'dashboard' => $dashboard,
			'stat'      => $stat,
			'form'      => $form->createView(),
			'form_route' => $form_route,
			'form_id'    => 'dashboard_widget_edit_form',
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

		$title = $dashboard_stat->getDisplayTitle();
		$title = strlen($title) ? $title : $dashboard_stat->getDefaultTitle();

		return array(
			'id' 		=> $dashboard_stat->getId(),
			'chart_vendor'	=> $chart->getViewChartVendor(),
			'chart_class'   => $chart->getViewChartClass(),
			'grid_slots'	=> $dashboard_stat->getGridSlots(),
			'grid_columns'  => $dashboard_stat->getGridColumns(),
			'grid_rows'     => $dashboard_stat->getGridRows(),
			'slot_number'   => $dashboard_stat->getSlotNumber(),
			'stat'		=> array(
				'id'	=> $stat->getId(),
				'title' => $title,
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

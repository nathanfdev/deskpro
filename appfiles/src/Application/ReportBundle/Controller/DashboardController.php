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
use Application\DeskPRO\Entity\StatDashboard;
use Application\ReportBundle\Form\EditStatDashboardType;

class DashboardController extends AbstractController
{
	
	public function indexAction()
	{		
		return $this->render('ReportBundle:Dashboard:index.html.twig');
	}
	
	public function viewAction($dashboard_id)
	{
		$dashboard = $this->getDashboard($dashboard_id);
		
		return $this->render('ReportBundle:Dashboard:view.html.twig', array(
			'dashboard' => $dashboard	
		));
	}
		
	public function editAction($dashboard_id)
	{
		if (!$dashboard_id) {
			$dashboard = new StatDashboard();
		} else {
			$dashboard = $this->getDashboard($dashboard_id);
		}

		$form = $this->get('form.factory')->create(new EditStatDashboardType(), $dashboard);

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
	 * Get the Dashboard Entity
	 *
	 * @throws NotFoundHttpException
	 */
	public function getDashboard($dashboard_id)
	{
		$dashboard = App::getEntityRepository('DeskPRO:StatDashboard')->find($dashboard_id);
		if (!$dashboard) {
			throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException("error_404_stat_dashboard");
		}
		
		return $dashboard;
	}
	
}

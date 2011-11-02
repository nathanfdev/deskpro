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
	
	public function newAction()
	{
		$dashboard = new StatDashboard();
		$form = $this->get('form.factory')->create(new EditStatDashboardType(), $dashboard);
		
		return $this->render('ReportBundle:Dashboard:new.html.twig', array(
			'form' => $form->createView(),
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

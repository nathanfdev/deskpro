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
use Application\ReportBundle\Form\EditStatType;

class TrendController extends AbstractController
{
	
	public function indexAction()
	{
		$stats = App::getEntityRepository('DeskPRO:Stat')->getEnabledStats();
		
		return $this->render('ReportBundle:Trend:index.html.twig', array(
			'stats' => $stats
		));
	}
	
	public function viewAction($stat_id)
	{
		$stat = $this->getStat($stat_id);
		
		return $this->render('ReportBundle:Trend:view.html.twig', array(
			'stat' => $stat	
		));
	}
	
	public function newAction()
	{
		
	}
	
	public function editAction($stat_id)
	{
		$stat = $this->getStat($stat_id);
		
		$form = $this->get('form.factory')->create(new EditStatType($stat->id ? false : true), $stat);

		if ($this->in->getBool('process')) {
			$form->bindRequest($this->get('request'));

			if ($form->isValid()) {
				App::getOrm()->persist($stat);
				App::getOrm()->flush();

				$this->session->setFlash('saved', $stat->title);
				return $this->redirectRoute('trend_index');
			}
		}
		
		return $this->render('ReportBundle:Trend:edit.html.twig', array(
			'stat' => $stat,
			'form' => $form->createView(),
		));
	}
	
	public function cloneAction($stat_id)
	{
		$stat = $this->getStat($stat_id);
		
		// TODO: clone it
		$cloned = $stat;
		
		return $this->redirectRoute('trend_edit', array('stat_id' => $cloned['id']));
	}
	
	protected function getStat($stat_id)
	{
		$stat = App::getEntityRepository('DeskPRO:Stat')->find($stat_id);
		if (!$stat) {
			throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException("error_404_stat");
		}
		
		return $stat;
	}
	
}

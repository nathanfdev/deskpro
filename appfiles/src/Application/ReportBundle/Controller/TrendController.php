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
use Application\ReportBundle\Stat\Base\AbstractStat;
use Application\DeskPRO\UI\RuleBuilder;

class TrendController extends AbstractController
{
	/**
	 * Show the list of trends. Starred trends first
	 */
	public function indexAction()
	{
		$stats = App::getEntityRepository('DeskPRO:Stat')->getEnabledStats();
		$dashboards = App::getEntityRepository('DeskPRO:ReportDashboard')->getDashboards();

		// The dates for pr
		$dates = AbstractStat::getDatesPast(time(), 3);

		return $this->render('ReportBundle:Trend:index.html.twig', array(
			'stats' 	    => $stats,
			'dashboards'	=> $dashboards,
			'dates'         => $dates,
		));
	}

	/**
	 * Show an actual trend
	 */
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

	/**
	 * Edit a trend
	 */
	public function editAction($stat_id)
	{
		$stat = $this->getStat($stat_id);

		$form = $this->get('form.factory')->create(new EditStatType($stat->id ? false : true), $stat);

		if ($this->in->getBool('process')) {
			$form->bindRequest($this->get('request'));

			if ($form->isValid()) {
				$term_rules = RuleBuilder::newTermsBuilder();
				$stat['criteria'] = $term_rules->readForm($this->in->getCleanValueArray('terms', 'raw' , 'discard'));

				App::getOrm()->persist($stat);
				App::getOrm()->flush();

				$this->session->setFlash('saved', $stat->title);
				return $this->createJsonResponse(array('success' => true));
			}
		}

		$term_options = App::getApi('tickets.search')->getSearchOptions($this->person);

		$ticket_field_defs = App::getApi('custom_fields.tickets')->getEnabledFields();
		$custom_fields = App::getApi('custom_fields.tickets')->getFieldsDisplayArray($ticket_field_defs);
		$term_options['custom_ticket_fields'] = $custom_fields;

		return $this->render('ReportBundle:Trend:edit.html.twig', array(
			'stat' => $stat,
			'form' => $form->createView(),
			'term_options' => $term_options,
		));
	}

	/**
	 * Clone an existing trend
	 */
	public function cloneAction($stat_id)
	{
		$stat = $this->getStat($stat_id);

		//$cloned = clone $stat;
		//$cloned->setId(null);
		//$cloned->setTitle("[Cloned] " . $cloned->getTitle());
		//$cloned->setAuthor($this->person);
		//
		//App::getOrm()->persist($cloned);
		//App::getOrm()->flush();

		return $this->redirectRoute('report_trend_edit', array('stat_id' => $stat['id']));
	}

	/**
	 * Get a Stat Entity by id
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

}

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
use Application\ReportBundle\Form\CloneStatType;
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
		$stats = $this->em->getRepository('DeskPRO:Stat')->getEnabledStats();

		foreach ($stats as $stat) {
			$end_date = new \DateTime();
			$points   = $stat->getDefaultDataPointCount();

			// Get the Stat Data
			$data = $stat->getData($end_date, $points);

			if (false === is_null($stat->getFormatter())) {
				// If there is a formatter, it may want to normalize the data
				$normalized_result = $stat->getFormatter()->normalizeData($data);
				$data 		= $normalized_result['data'];
				$display_unit 	= $normalized_result['unit'];

				$stat->setData($data);
				$stat->setDisplayUnits($display_unit);
			}
		}

		return $this->render('ReportBundle:Trend:index.html.twig', array(
			'stats' 	=> $stats,
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

		$form = $this->get('form.factory')->create(new EditStatType(), $stat);

		if ($this->in->getBool('process')) {
			$form->bindRequest($this->get('request'));

			if ($form->isValid()) {
				$this->em->persist($stat);
				$this->em->flush();

				$this->session->setFlash('saved', $stat->title);
				$redirect_url = $this->generateUrl('report_trend_index');
				return $this->createJsonResponse(array('success' => true, 'redirect' => $redirect_url));
			}
		}

		return $this->render('ReportBundle:Trend:edit.html.twig', array(
			'stat' => $stat,
			'form' => $form->createView(),
		));
	}

	/**
	 * Clone an existing trend
	 */
	public function cloneAction($stat_id)
	{
		$stat = $this->getStat($stat_id);

		$form = $this->get('form.factory')->create(new CloneStatType(), $stat);

		if ($this->in->getBool('process')) {
			$form->bindRequest($this->get('request'));

			if ($form->isValid()) {
				$cloned = clone $stat;
				$cloned->setId(null);
				$cloned->setTitle($cloned->getTitle());
				$cloned->setAuthor($this->person);

				$term_rules = RuleBuilder::newTermsBuilder();
				$cloned['criteria'] = $term_rules->readForm($this->in->getCleanValueArray('terms', 'raw' , 'discard'));

				$this->em->persist($cloned);
				$this->em->flush();

				$this->session->setFlash('saved', $stat->title);
				$redirect_url = $this->generateUrl('report_trend_index');
				return $this->createJsonResponse(array('success' => true, 'redirect' => $redirect_url));
			}
		}

		$term_options = App::getApi('tickets.search')->getSearchOptions($this->person);

		$ticket_field_defs = App::getApi('custom_fields.tickets')->getEnabledFields();
		$custom_fields = App::getApi('custom_fields.tickets')->getFieldsDisplayArray($ticket_field_defs);
		$term_options['custom_ticket_fields'] = $custom_fields;

		return $this->render('ReportBundle:Trend:clone.html.twig', array(
			'stat' => $stat,
			'form' => $form->createView(),
			'term_options' => $term_options,
		));
	}

	/**
	 * Get a Stat Entity by id
	 *
	 * @throws NotFoundHttpException
	 */
	protected function getStat($stat_id)
	{
		$stat = $this->em->getRepository('DeskPRO:Stat')->find($stat_id);
		if (!$stat) {
			throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException("error_404_stat");
		}

		return $stat;
	}

}

<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage TechBundle
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris@nadeau.ws>
 */

namespace Application\TechBundle\Controller;

use \Application\CoreBundle\Entity\TicketQueue;
use \DeskPRO\App;
use \Orb\Util\Strings;
use \Orb\Util\Arrays;

/**
 * Handles ticket searches
 */
class TicketSearchController extends AbstractController
{
	public function indexAction()
	{
		return $this->render('TechBundle:TicketSearch:search.twig');
	}

	public function filtersPaneAction()
	{
		return $this->render('TechBundle:TicketSearch:pane-filters.twig');
	}

	public function runFilterAction($filter_id)
	{
		return $this->render('TechBundle:TicketSearch:search.twig');
	}

	public function ticketViewAction()
	{
		$person_inner_tab = $this->forward('TechBundle:Person:view', array('person_id' => 1))->getContent();

		return $this->render('TechBundle:TicketSearch:ticket-view.twig', array(
			'person_inner_tab' => $person_inner_tab
		));
	}

	############################################################################
	# /tech/ticket-search/queues/list                     tech_ticketqueues_list
	############################################################################

	/**
	 * Just a list of filters
	 */
	public function listQueuesAction()
	{
		$queues = $this->em->createQuery("
			SELECT q
			FROM CoreBundle:TicketQueue q
			WHERE q.is_global = true OR q.person_id = ?1
		")->setParameter(1, $this->person['id'])->execute();

		return $this->render('TechBundle:TicketSearch:queues-list.twig', array(
			'queues' => $queues
		));
	}



	############################################################################
	# /tech/ticket-search/queues/:queue_id/edit           tech_ticketqueues_edit
	############################################################################

	/**
	 * Edit a queue
	 */
	public function editQueueAction($queue_id)
	{
		if ($queue_id) {
			try {
				$queue = $this->em->find('CoreBundle:TicketQueue', $queue_id);
			} catch (\Doctrine\ORM\NoResultException $e) {
				throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException("There is no queue with ID $queue_id");
			}
		} else {
			$queue = new TicketQueue;
		}

		$term_options = array();
		$term_options['agents']      = $this->em->getRepository('CoreBundle:Person')->getAgentNames();
		$term_options['products']    = $this->em->getRepository('CoreBundle:Product')->getProductNames();
		$term_options['departments'] = $this->em->getRepository('CoreBundle:Department')->getDepartmentNames();
		$term_options['categories']  = $this->em->getRepository('CoreBundle:TicketCategory')->getAllCategoryNames();
		$term_options['priorities']  = $this->em->getRepository('CoreBundle:TicketPriority')->getPriorityNames();

		if ($this->isPostRequest()) {
			$errors = $this->_processEditQueue($queue);
			if (!$errors) {
				echo "Saved!";
			}
		}

		return $this->render('TechBundle:TicketSearch:queues-edit.twig', array(
			'term_options' => $term_options,
			'queue' => $queue
		));
	}

	public function _processEditQueue(TicketQueue $queue)
	{
		$queue['title'] = $this->in->getString('title');
		$queue['terms'] = $this->in->getCleanValueArray('terms', 'raw' , 'discard');

		if (!$queue['person_id']) {
			$queue['person'] = $this->person;
		}

		$queue['is_global'] = true;
		$queue['is_enabled'] = true;

		$this->em->persist($queue);
		$this->em->flush();

		return null;
	}
}
<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage AgentBundle
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\AgentBundle\Controller;

use \Application\DeskPRO\Entity\TicketQueue;
use \Application\DeskPRO\App;
use \Orb\Util\Strings;
use \Orb\Util\Arrays;

/**
 * Handles ticket searches
 */
class TicketSearchController extends AbstractController
{
	public function indexAction()
	{
		return $this->render('AgentBundle:TicketSearch:list-blank.twig');
	}

	public function filtersPaneAction()
	{
		$queues = App::getApi('tickets.queues')->getQueuesForPerson($this->person);

		if ($this->in->getStringFromCookie('dpa_queue_order')) {
			$order = explode(',', $this->in->getStringFromCookie('dpa_queue_order'));
			$queues_unordered = $queues;
			$queues = array();

			foreach ($order as $id) {
				$queues[$id] = $queues_unordered[$id];
				unset($queues_unordered[$id]);
			}

			if (count($queues_unordered)) {
				foreach ($queues_unordered as $id => $q) {
					$queues[$id] = $q;
				}
			}
		}

		return $this->render('AgentBundle:TicketSearch:pane-filters.twig', array(
			'filters' => $queues
		));
	}

	public function runFilterAction($filter_id)
	{
		$page = $this->in->getUint('page');
		if (!$page) $page = 1;

		$tickets = App::getApi('tickets.queues')->getTicketsFromQueue($filter_id, $page, 50);

		$tpl = 'AgentBundle:TicketSearch:filter-results.twig';
		if ($this->in->getBool('partial')) {
			$tpl = 'AgentBundle:TicketSearch:filter-results-list.twig';
			if (!count($tickets)) {
				return $this->createResponse('');
			}
		}

		return $this->render($tpl, array(
			'queue_id' => $filter_id,
			'tickets' => $tickets,
			'page' => $page
		));
	}

	public function flaggedPaneAction()
	{
		return $this->render('AgentBundle:TicketSearch:pane-flagged.twig', array(

		));
	}

	public function runFlaggedAction($flag)
	{
		$page = $this->in->getUint('page');
		if (!$page) $page = 1;

		$tickets = App::getApi('tickets.queues')->getTicketsFromFlagged($flag, $page, 50);

		$tpl = 'AgentBundle:TicketSearch:flagged-results.twig';
		if ($this->in->getBool('partial')) {
			$tpl = 'AgentBundle:TicketSearch:filter-results-list.twig';
			if (!count($tickets)) {
				return $this->createResponse('');
			}
		}

		return $this->render($tpl, array(
			'flag' => $flag,
			'tickets' => $tickets,
			'page' => $page
		));
	}

	############################################################################
	# /agent/ticket-search/queues/list                     agent_ticketqueues_list
	############################################################################

	/**
	 * Just a list of filters
	 */
	public function listQueuesAction()
	{
		$queues = App::getApi('tickets.queues')->getQueuesForPerson($this->person);

		return $this->render('AgentBundle:TicketSearch:queues-list.twig', array(
			'queues' => $queues
		));
	}



	############################################################################
	# /agent/ticket-search/queues/:queue_id/edit           agent_ticketqueues_edit
	############################################################################

	/**
	 * Edit a queue
	 */
	public function editQueueAction($queue_id)
	{
		if ($queue_id) {
			try {
				$queue = $this->em->find('DeskPRO:TicketQueue', $queue_id);
			} catch (\Doctrine\ORM\NoResultException $e) {
				throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException("There is no queue with ID $queue_id");
			}
		} else {
			$queue = new TicketQueue;
		}

		$term_options = App::getApi('tickets.search')->getSearchOptions($this->person);

		if ($this->isPostRequest()) {
			$errors = $this->_processEditQueue($queue);
			if (!$errors) {
				echo "Saved!";
			}
		}

		return $this->render('AgentBundle:TicketSearch:queues-edit.twig', array(
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
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

	public function queuesPaneAction()
	{
		$queues = App::getApi('tickets.queues')->getQueuesForPerson($this->person);

		$order = $this->person->getPref('agent.ui.ticket-queues-order');
		if ($order) {
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

		return $this->render('AgentBundle:TicketSearch:pane-queues.twig', array(
			'queues' => $queues
		));
	}

	public function runQueueAction($queue_id)
	{
		$page = $this->in->getUint('page');
		if (!$page) $page = 1;

		$tickets = App::getApi('tickets.queues')->getTicketsFromQueue($queue_id, $page, 50);

		$tpl = 'AgentBundle:TicketSearch:queue-results.twig';
		if ($this->in->getBool('partial')) {
			$tpl = 'AgentBundle:TicketSearch:queue-results-list.twig';
			if (!count($tickets)) {
				return $this->createResponse('');
			}
		}

		$display_fields = $this->person->getPref('agent.ui.ticket-queues-display-fields.' . $queue_id);

		if (!$display_fields) {
			$display_fields = array('person', 'department');
		}

		return $this->render($tpl, array(
			'queue_id' => $queue_id,
			'tickets' => $tickets,
			'page' => $page,
			'display_fields' => $display_fields
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

		$tickets = App::getApi('tickets.queues')->getTicketsFromFlagged($flag, $this->person, $page, 50);

		$tpl = 'AgentBundle:TicketSearch:flagged-results.twig';
		if ($this->in->getBool('partial')) {
			$tpl = 'AgentBundle:TicketSearch:queue-results-list.twig';
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
	 * Just a list of queues
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
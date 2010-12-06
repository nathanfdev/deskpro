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
		return $this->render('TechBundle:TicketSearch:list-blank.twig');
	}

	public function filtersPaneAction()
	{
		$queues = App::getApi('tickets.queues')->getQueuesForPerson($this->person);

		return $this->render('TechBundle:TicketSearch:pane-filters.twig', array(
			'filters' => $queues
		));
	}

	public function runFilterAction($filter_id)
	{
		$page = $this->in->getUint('page');
		if (!$page) $page = 1;

		$tickets = App::getApi('tickets.queues')->getTicketsFromQueue($filter_id, $page, 25);

		return $this->render('TechBundle:TicketSearch:filter-results.twig', array(
			'tickets' => $tickets
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
		$queues = App::getApi('tickets.queues')->getQueuesForPerson($this->person);

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
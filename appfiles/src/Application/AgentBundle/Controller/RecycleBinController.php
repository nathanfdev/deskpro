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

use \Orb\Util\Arrays;

use \Application\DeskPRO\Entity;
use \Application\DeskPRO\Entity\Person;
use \Application\DeskPRO\Entity\PersonEmail;
use \Application\DeskPRO\Entity\PersonContactData;
use \Application\DeskPRO\Entity\PersonNote;
use \Application\DeskPRO\Entity\Organization;

use \Application\DeskPRO\App;

/**
 * Handles viewing of deleted items
 */
class RecycleBinController extends AbstractController
{
	public function listAction()
	{
		$tickets = $this->_getTickets();
		$ticket_html = false;
		if (!empty($tickets['html'])) {
			$ticket_html = $tickets['html'];
		}

		return $this->render('AgentBundle:RecycleBin:list.html.twig', array(
			'tickets_html' => $ticket_html,
			'tickets_no_more_results' => $tickets['no_more_results'],
		));
	}

	public function listMoreAction($type, $page)
	{
		$method = '_get' . ucfirst($type);
		$res = $this->$method($page);

		$return_res = array(
			'html' => $res['html'],
			'count' => $res['count'],
			'no_more_results' => $res['no_more_results']
		);

		return $this->createJsonResponse($res);
	}


	############################################################################
	# fetcher methods for different types
	############################################################################

	protected function _getTickets($page = 1)
	{
		$per_page = 10;
		$pageinfo = array(
			'limit'  => $per_page,
			'offset' => ($page - 1) * $per_page
		);

		$searcher = new \Application\DeskPRO\Searcher\TicketSearch();
		$searcher->addTerm('deleted', 'is', 1);

		$results = $searcher->getMatches($pageinfo);
		$results = array();

		if (!$results) {
			return array('no_more_results' => true);
		}

		$no_more = false;
		if (count($results < $per_page)) {
			$no_more = true;
		}

		$deleted_tickets = App::getOrm()->createQuery("
			SELECT d
			FROM DeskPRO:TicketDeleted d INDEX BY d.ticket_id
			LEFT JOIN d.by_person p
			WHERE d.ticket_id IN (" . implode(',', $results) . ")
		");
		$tickets = App::getEntityRepository('DeskPRO:Ticket')->getTicketsFromIds($results);


		$vars = array(
			'tickets' => $tickets,
			'count' => count($tickets),
			'deleted_tickets' => $deleted_tickets,
			'page' => $page,
			'no_more_results' => $no_more
		);

		$vars['html'] = $this->renderView('AgentBundle:RecycleBin:list-tickets.html.twig', $vars);

		return $vars;
	}
}
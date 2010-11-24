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

/**
 * Handles ticket searches
 */
class TicketSearchController extends AbstractController
{
	public function indexAction()
	{
		return $this->render('TechBundle:TicketSearch:search');
	}

	public function filtersPaneAction()
	{
		return $this->render('TechBundle:TicketSearch:pane-filters');
	}

	public function runFilterAction($filter_id)
	{
		return $this->render('TechBundle:TicketSearch:search');
	}

	public function ticketViewAction()
	{
		$person_inner_tab = $this->forward('TechBundle:Person:view', array('person_id' => 1))->getContent();

		return $this->render('TechBundle:TicketSearch:ticket-view', array(
			'person_inner_tab' => $person_inner_tab
		));
	}
}
<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage UserBundle
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\UserBundle\Controller;

use \Application\DeskPRO\App;
use \Application\DeskPRO\Entity;

use \Orb\Util\Arrays;

use \Application\UserBundle\Form\NewTicketForm;
use \Application\UserBundle\Form\NewTicketUserForm;

class TicketsController extends AbstractController
{
	################################################################################
	# list
	################################################################################

	/**
	 * View a list of all tickets
	 */
    public function listAction()
    {
		$tickets = App::getOrm()->createQuery("
			SELECT ticket
			FROM DeskPRO:Ticket ticket
			WHERE ticket.person = :person
			ORDER BY ticket.id DESC
		")->execute(array('person' => $this->person));

        return $this->render('UserBundle:Tickets:list.html.twig', array(
			'tickets' => $tickets
		));
    }



	################################################################################
	# view
	################################################################################

	/**
	 * View a ticket
	 */
	public function viewAction($ticket_id)
	{
		$ticket = $this->getTicketOr404($ticket_id);

		// Custom fields
		$ticket_field_defs = App::getApi('custom_fields.tickets')->getEnabledFields();
		$ticket_data_structured = App::getApi('custom_fields.util')->createDataHierarchy($ticket['custom_data'], $ticket_field_defs);
		$custom_fields = App::getApi('custom_fields.tickets')->getFieldsDisplayArray($ticket_field_defs, $ticket_data_structured);

		// Widgets
		$widget_recs = App::getEntityRepository('DeskPRO:Widget')->getWidgetsForSection('user.ticket');
		$widgets = array();
		if (count($widget_recs)) {
			$widgets = \Application\DeskPRO\Widgets\Factory::createHandlersForWidgets(
				$widget_recs,
				array('ticket' => $ticket, 'person' => $this->person)
			);
		}

		$widgets = Arrays::groupItems($widgets, 'section', true);
		if (!isset($widgets['user.ticket.display'])) $widgets['user.ticket.display'] = array();

		return $this->render('UserBundle:Tickets:view.html.twig', array(
			'ticket' => $ticket,
			'custom_fields' => $custom_fields,
			'widgets' => $widgets
		));
	}

	/**
	 * @return Application\DeskPRO\Entity\Ticket
	 */
	protected function getTicketOr404($ticket_id)
	{
		$ticket = App::getEntityRepository('DeskPRO:Ticket')->find($ticket_id);

		if (!$ticket OR $ticket['person_id'] != $this->person['id']) {
			throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException("There is no ticket with ID $ticket_id");
		}

		return $ticket;
	}
}

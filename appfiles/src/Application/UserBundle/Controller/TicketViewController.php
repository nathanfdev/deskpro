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

use Application\DeskPRO\App;
use Application\DeskPRO\Entity\Ticket;

use Application\DeskPRO\Tickets\TicketDisplay;
use Application\UserBundle\Form\NewTicketReplyType;

use Orb\Util\Arrays;
use Orb\Util\Numbers;

class TicketViewController extends AbstractController
{
	###########################################################################
	# load
	###########################################################################

	/**
	 * Loader action takes one of: ticket ID, ref, TAC or PTAC.
	 *
	 * With ID or Ref, the user must be logged in. TAC or PTAC shows the
	 * "guest view" of a ticket.
	 *
	 * @param string|int $ticket_id
	 */
	public function loadAction($ticket_ref, array $display_data = array())
	{
		if ($this->person['id']) {
			$try_order = array('id', 'ref', 'ptac');
		} else {
			// People who arent logged in we can try ptac first to save a query
			// (since ref and ptac could look smiliar based on ref format, we'd have to check two)
			$try_order = array('id', 'ptac', 'ref');
		}

		foreach ($try_order as $lookup_type) {
			switch ($lookup_type) {
				case 'id':
					if (Numbers::isInteger($ticket_ref)) {
						$ticket = App::findEntity('DeskPRO:Ticket', $ticket_ref);
						return $this->viewTicket($ticket, $display_data);
					}
					break;

				case 'ref':
					$ref_gen = $this->get('deskpro.ref_generator');
					if ($ref_gen->isRefMatch($ticket_ref)) {
						$ticket = App::getEntityRepository('DeskPRO:Ticket')->findOneByRef($ticket_ref);
						if ($ticket) {
							return $this->viewTicket($ticket, $display_data);
						}
					}
					break;

				case 'ptac':
					$ticket = App::getEntityRepository('DeskPRO:Ticket')->getByAccessCode($ticket_ref);
					if ($ticket) {
						return $this->viewGuestTicket($ticket, $display_data);
					}
					break;
			}
		}

		return $this->createNotFoundException();
	}

	###########################################################################
	# viewTicket
	###########################################################################

	/**
	 * View a ticket without a user session
	 *
	 * @param \Application\DeskPRO\Entity\Ticket $ticket
	 */
	public function viewTicket(Ticket $ticket, array $display_data = array())
	{
		$ticket_display = new TicketDisplay($ticket, $this->person);
		$vars = $ticket_display->getDisplayArray();

		$newreply_form = $this->get('form.factory')->create(new NewTicketReplyType());
		$vars['newreply_form'] = $newreply_form->createView();

		if ($display_data) {
			$vars = array_merge($vars, $display_data);
		}

		return $this->render('UserBundle:TicketView:view.html.twig', $vars);
	}


	###########################################################################
	# viewGuestTicket
	###########################################################################

	/**
	 * View a ticket without a user session
	 *
	 * @param \Application\DeskPRO\Entity\Ticket $ticket
	 */
	public function viewGuestTicket(Ticket $ticket)
	{
		$ticket_display = new TicketDisplay($ticket, $this->person);
		$vars = $ticket_display->getDisplayArray();

		return $this->render('UserBundle:TicketView:view-guest.html.twig', $vars);
	}
}

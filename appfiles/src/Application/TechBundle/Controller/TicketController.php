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
class TicketController extends AbstractController
{
	public function viewAction($ticket_id)
	{
		$ticket = $this->em->getRepository('DeskPRO:Ticket')->find($ticket_id);
		$person_inner_tab = $this->forward('TechBundle:Person:view', array('person_id' => $ticket['person_id']))->getContent();

		return $this->render('TechBundle:Ticket:view.twig', array(
			'person_inner_tab' => $person_inner_tab,
			'ticket' => $ticket
		));
	}
}
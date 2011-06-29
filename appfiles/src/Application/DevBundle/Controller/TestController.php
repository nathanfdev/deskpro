<?php

namespace Application\DevBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use \Application\DeskPRO\App;
use \Application\DeskPRO\Entity\ClientMessage;

use \Orb\Util\Strings;

class TestController extends Controller
{
    public function indexAction()
    {
		echo '<pre>';

		/** @var $ticket \Application\DeskPRO\Entity\Ticket */
		$ticket = App::findEntity('DeskPRO:Ticket', 12029);
		$ticket['agent'] = null;
		App::getOrm()->persist($ticket);
		App::getOrm()->flush();

		/** @var $logger \Application\DeskPRO\Tickets\TicketChangeTracker */
		$logger = $ticket->getTicketLogger();

		$ticket['agent_id'] = 20001;
		App::getOrm()->persist($ticket);
		App::getOrm()->flush();

		//print_r($logger->getListUpdater()->changed_fields);

		exit;
		return $this->render('DevBundle:Test:test.html.twig');
    }
}

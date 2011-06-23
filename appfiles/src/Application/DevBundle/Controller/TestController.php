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

		$ticket_page = new \Application\DeskPRO\PageDisplay\Page\TicketPageZoneCollection('agent');
		$ticket_page->addPagesFromDb();
		echo $ticket_page->compileJs();
		exit;

		//$ticket_display = App::findEntity('DeskPRO:TicketPageDisplay', 40);
		$ticket_display = App::findEntity('DeskPRO:TicketPageDisplay', 42);
		print_r($ticket_display['data']);

		exit;
		return $this->render('DevBundle:Test:test.html.twig');
    }
}

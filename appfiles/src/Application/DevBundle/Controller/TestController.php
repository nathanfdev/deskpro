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

		$filters = App::getEntityRepository('DeskPRO:TicketFilter')->getAllForActiveAgents();

		echo count($filters);

		exit;
		return $this->render('DevBundle:Test:test.html.twig');
    }
}

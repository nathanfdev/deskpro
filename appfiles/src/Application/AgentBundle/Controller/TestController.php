<?php

namespace Application\AgentBundle\Controller;

use \Application\DeskPRO\App;
use \Orb\Util\Arrays;

class TestController extends AbstractController
{
    public function indexAction()
    {
		echo '<pre>';
		print_r(App::getEntityRepository('DeskPRO:TicketCategory')->departmentToCategoryMap());

		exit;
    }
}

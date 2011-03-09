<?php

namespace Application\AgentBundle\Controller;

use \Application\DeskPRO\Entity;
use \Application\DeskPRO\App;
use \Orb\Util\Arrays;
use \Orb\Util\Strings;

class TestController extends AbstractController
{
    public function indexAction()
    {
		$filter = APp::getEntityRepository('DeskPRO:TicketFilter')->find(5);

		$s = $filter->getSearcher();
		print_r($s->getSummary());

		exit;
    }
}

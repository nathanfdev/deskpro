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
		sleep(30);
		return $this->render('AgentBundle:Test:test.html.twig');
	}
}

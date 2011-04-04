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
		$v = App::getCache('common')->load('department_info');
		print_r($v);

		exit;
    }
}

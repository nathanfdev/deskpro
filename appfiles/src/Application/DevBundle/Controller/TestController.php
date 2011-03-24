<?php

namespace Application\DevBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use \Application\DeskPRO\App;
use \Application\DeskPRO\Entity;

use \Orb\Util\Strings;

class TestController extends Controller
{
    public function indexAction()
    {
		$p = App::getEntityRepository('DeskPRO:Person')->find(6535);
		echo $p->getDisplayContactShort(10);
		exit;
    }
}

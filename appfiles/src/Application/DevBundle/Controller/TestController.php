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
		echo Strings::utf8_strtolower('ABC');
		exit;
    }
}

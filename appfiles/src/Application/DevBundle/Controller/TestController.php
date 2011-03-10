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
		$test = new \Zend_Oauth_Consumer();
		$test = new \Zend_Oauth_Consumer();
		$test2 = new \Zend_Service_Twitter();
		exit;
    }
}

<?php

namespace Application\DevBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use \Application\DeskPRO\App;
use \Application\DeskPRO\Entity;
use \Application\DeskPRO\Entity\IdeaCategory;

use \Orb\Util\Strings;

class TestController extends Controller
{
    public function indexAction()
    {
		return $this->render('DevBundle:Test:test.html.twig');
    }
}

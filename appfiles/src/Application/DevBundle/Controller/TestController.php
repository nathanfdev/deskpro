<?php

namespace Application\DevBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use \Application\DeskPRO\App;
use \Application\DeskPRO\Entity\Person;

use \Orb\Util\Strings;

class TestController extends Controller
{
    public function indexAction()
    {
		$person = new Person;
		$person['first_name'] = "TEST";

		App::getOrm()->persist($person);
		App::getOrm()->flush();

		exit;
		return $this->render('DevBundle:Test:test.html.twig');
    }
}

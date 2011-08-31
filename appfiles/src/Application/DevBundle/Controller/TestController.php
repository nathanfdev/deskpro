<?php

namespace Application\DevBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity\ClientMessage;

use Orb\Util\Strings;
use Orb\Util\Arrays;

class TestController extends Controller
{
    public function indexAction()
    {
		$ids = App::getEntityRepository('DeskPRO:Rating')->getRatingsFor('idea', 2);

		echo count($ids);
		print_r(array_keys($ids));

		exit;
		return $this->render('DevBundle:Test:test.html.twig');
    }
}

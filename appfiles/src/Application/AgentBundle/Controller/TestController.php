<?php

namespace Application\AgentBundle\Controller;

use \Application\DeskPRO\App;
use \Orb\Util\Arrays;

class TestController extends AbstractController
{
    public function indexAction()
    {
		echo '<pre>';

		$array = array(0, 1, 2, 3, 4, 5, 6, 7, 8, 9);

		print_r(Arrays::getPageChunk($array, 2, 3));

		exit;
    }
}

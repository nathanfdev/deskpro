<?php

namespace Application\DevBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use \Application\DeskPRO\App;
use \Application\DeskPRO\Entity;

class TestController extends Controller
{
    public function indexAction()
    {
		$cache = new \Orb\Doctrine\Common\Cache\SqliteCache();
		$cache->setDbFile('MEMORY', 'cache1', 'main');
		$cache->save('test', 'Woo!');
		echo $cache->fetch('test');
		exit;
    }
}

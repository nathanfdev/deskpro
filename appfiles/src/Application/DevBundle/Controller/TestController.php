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
		$cats = App::getOrm()->createQuery("
			SELECT c
			FROM DeskPRO:ArticleCategory c INDEX BY c.id
			ORDER BY c.display_order
		")->execute();

		$cats[2]['parent'] = $cats[1];
		$cats[3]['parent'] = $cats[1];

		$cats[8]['parent'] = $cats[7];
		$cats[9]['parent'] = $cats[7];

		App::getOrm()->persist($cats[2]);
		App::getOrm()->persist($cats[3]);
		App::getOrm()->persist($cats[8]);
		App::getOrm()->persist($cats[9]);
		App::getOrm()->flush();

		exit;
		return $this->render('DevBundle:Test:test.html.twig');
    }
}

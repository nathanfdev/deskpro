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
		foreach (array('ArticleCategory', 'FeedbackCategory', 'NewsCategory', 'DownloadCategory', 'Product') as $name) {
			$name = "DeskPRO:$name";
			$er = App::getEntityRepository($name);
			$er->repair();
		}
		exit;
		return $this->render('DevBundle:Test:test.html.twig');
    }
}

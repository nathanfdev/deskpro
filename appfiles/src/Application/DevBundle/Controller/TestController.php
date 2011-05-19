<?php

namespace Application\DevBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use \Application\DeskPRO\App;
use \Application\DeskPRO\Entity\Product;

use \Orb\Util\Strings;

class TestController extends Controller
{
    public function indexAction()
    {
		$cat01 = new Product();
		$cat01['title'] = "Product 1";
			$cat02 = new Product();
			$cat02['parent'] = $cat01;
			$cat02['title'] = "Sub 1";
			$cat03 = new Product();
			$cat03['parent'] = $cat01;
			$cat03['title'] = "Sub 2";
		$cat04 = new Product();
		$cat04['title'] = "Product 2";
			$cat05 = new Product();
			$cat05['parent'] = $cat04;
			$cat05['title'] = "Sub 3";
			$cat06 = new Product();
			$cat06['parent'] = $cat04;
			$cat06['title'] = "Sub 4";
		$cat07 = new Product();
		$cat07['title'] = "Product 3";
		$cat08 = new Product();
		$cat08['title'] = "Product 4";

		for ($i = 1; $i <= 8; $i++) {
			$c = ${"cat0$i"};

			App::getOrm()->persist($c);
		}

		App::getOrm()->flush();

		exit;
		return $this->render('DevBundle:Test:test.html.twig');
    }
}

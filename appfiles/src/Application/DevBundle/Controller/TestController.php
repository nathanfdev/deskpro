<?php

namespace Application\DevBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use \Application\DeskPRO\App;
use \Application\DeskPRO\Entity;
use \Application\DeskPRO\Entity\ArticleCategory;

use \Orb\Util\Strings;

class TestController extends Controller
{
    public function indexAction()
    {
		$cat = App::getEntityRepository('DeskPRO:ArticleCategory')->find(9);

		//$ids = App::getEntityRepository('DeskPRO:ArticleCategory')->childrenIds($cat);
		$ids = $cat->getTreeIds();

		print_r($ids);

		exit;
		$cat1 = new ArticleCategory();
		$cat1['title'] = 'Colors';
			$cat2 = new ArticleCategory();
			$cat2['title'] = 'Red';
			$cat2->parent = $cat1;

		App::getOrm()->persist($cat1);
		App::getOrm()->persist($cat2);
		App::getOrm()->flush();
		exit;
		$cat1 = new ArticleCategory();
		$cat1['title'] = 'Test 1';
			$cat2 = new ArticleCategory();
			$cat2['title'] = 'Test 1A';
			$cat2->parent = $cat1;
			$cat3 = new ArticleCategory();
			$cat3['title'] = 'Test 1B';
			$cat3->parent = $cat1;
				$cat4 = new ArticleCategory();
				$cat4['title'] = 'Test 1B A';
				$cat4->parent = $cat3;
			$cat5 = new ArticleCategory();
			$cat5['title'] = 'Test 1C';
			$cat5->parent = $cat1;
		$cat6 = new ArticleCategory();
		$cat6['title'] = 'Test 2';
		$cat7 = new ArticleCategory();
		$cat7['title'] = 'Test 3';
		$cat8 = new ArticleCategory();
		$cat8['title'] = 'Test 4';
		$cat9 = new ArticleCategory();
		$cat9['title'] = 'Test 5';
			$cat10 = new ArticleCategory();
			$cat10['title'] = 'Test 5A';
			$cat10->parent = $cat9;
			$cat11 = new ArticleCategory();
			$cat11['title'] = 'Test 5B';
			$cat11->parent = $cat9;
			$cat12 = new ArticleCategory();
			$cat12['title'] = 'Test 5C';
			$cat12->parent = $cat9;
		$cat13 = new ArticleCategory();
		$cat13['title'] = 'Test 5B';

		for ($i = 1; $i <= 13; $i++) {
			$c = ${"cat$i"};
			App::getOrm()->persist($c);
		}

		App::getOrm()->flush();

		exit;
    }
}

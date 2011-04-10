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
		$p = App::getEntityRepository('DeskPRO:Person')->find(20001);

		App::getOrm()->beginTransaction();

		for ($i = 1; $i <= 20; $i++) {
			$cat = App::getEntityRepository('DeskPRO:IdeaCategory')->find(mt_rand(1,13));
			$idea = new Entity\Idea;
			$idea['category'] = $cat;
			$idea['person'] = $p;
			$idea['title'] = "Test Idea $i";
			$idea['content'] = "test idea $i";
			$idea['status'] = 'new';
			$idea['date_created'] = new \DateTime();

			App::getOrm()->persist($idea);
		}

		App::getOrm()->flush();
		App::getOrm()->commit();
		exit;
		$cat1 = new IdeaCategory();
		$cat1['title'] = 'Test 1';
			$cat2 = new IdeaCategory();
			$cat2['title'] = 'Test 1A';
			$cat2->parent = $cat1;
			$cat3 = new IdeaCategory();
			$cat3['title'] = 'Test 1B';
			$cat3->parent = $cat1;
				$cat4 = new IdeaCategory();
				$cat4['title'] = 'Test 1B A';
				$cat4->parent = $cat3;
			$cat5 = new IdeaCategory();
			$cat5['title'] = 'Test 1C';
			$cat5->parent = $cat1;
		$cat6 = new IdeaCategory();
		$cat6['title'] = 'Test 2';
		$cat7 = new IdeaCategory();
		$cat7['title'] = 'Test 3';
		$cat8 = new IdeaCategory();
		$cat8['title'] = 'Test 4';
		$cat9 = new IdeaCategory();
		$cat9['title'] = 'Test 5';
			$cat10 = new IdeaCategory();
			$cat10['title'] = 'Test 5A';
			$cat10->parent = $cat9;
			$cat11 = new IdeaCategory();
			$cat11['title'] = 'Test 5B';
			$cat11->parent = $cat9;
			$cat12 = new IdeaCategory();
			$cat12['title'] = 'Test 5C';
			$cat12->parent = $cat9;
		$cat13 = new IdeaCategory();
		$cat13['title'] = 'Test 5B';

		for ($i = 1; $i <= 13; $i++) {
			$c = ${"cat$i"};
			App::getOrm()->persist($c);
		}

		App::getOrm()->flush();

		exit;
    }
}

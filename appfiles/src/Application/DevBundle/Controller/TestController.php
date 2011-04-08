<?php

namespace Application\DevBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use \Application\DeskPRO\App;
use \Application\DeskPRO\Entity;
use \Application\DeskPRO\Entity\DownloadCategory;

use \Orb\Util\Strings;

class TestController extends Controller
{
    public function indexAction()
    {
		$p = App::getEntityRepository('DeskPRO:Person')->find(20001);

		App::getDb()->beginTransaction();

		for ($i = 0; $i < 15; $i++) {

			$cat = App::getEntityRepository('DeskPRO:NewsCategory')->find(mt_rand(1,6));

			$post = new Entity\News();
			$post['person'] = $p;
			$post['category'] = $cat;
			$post['title'] = "Post #$i";
			$post['content'] = "testing a post";
			$post['date_created'] = new \DateTime();

			App::getOrm()->persist($post);
			App::getOrm()->flush();
		}

		App::getDb()->commit();

		exit;
		$cat1 = new DownloadCategory();
		$cat1['title'] = 'Test 1';
			$cat2 = new DownloadCategory();
			$cat2['title'] = 'Test 1A';
			$cat2->parent = $cat1;
			$cat3 = new DownloadCategory();
			$cat3['title'] = 'Test 1B';
			$cat3->parent = $cat1;
				$cat4 = new DownloadCategory();
				$cat4['title'] = 'Test 1B A';
				$cat4->parent = $cat3;
			$cat5 = new DownloadCategory();
			$cat5['title'] = 'Test 1C';
			$cat5->parent = $cat1;
		$cat6 = new DownloadCategory();
		$cat6['title'] = 'Test 2';
		$cat7 = new DownloadCategory();
		$cat7['title'] = 'Test 3';
		$cat8 = new DownloadCategory();
		$cat8['title'] = 'Test 4';
		$cat9 = new DownloadCategory();
		$cat9['title'] = 'Test 5';
			$cat10 = new DownloadCategory();
			$cat10['title'] = 'Test 5A';
			$cat10->parent = $cat9;
			$cat11 = new DownloadCategory();
			$cat11['title'] = 'Test 5B';
			$cat11->parent = $cat9;
			$cat12 = new DownloadCategory();
			$cat12['title'] = 'Test 5C';
			$cat12->parent = $cat9;
		$cat13 = new DownloadCategory();
		$cat13['title'] = 'Test 5B';

		for ($i = 1; $i <= 13; $i++) {
			$c = ${"cat$i"};
			App::getOrm()->persist($c);
		}

		App::getOrm()->flush();

		exit;
    }
}

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
		$db = App::getDb();

		$db->exec("TRUNCATE TABLE label_defs");
		$db->exec("TRUNCATE TABLE labels_articles");
		$db->exec("TRUNCATE TABLE labels_downloads");
		$db->exec("TRUNCATE TABLE labels_ideas");
		$db->exec("TRUNCATE TABLE labels_news");

		$words = array(
			'address', 'law', 'sentry', 'composer', 'mat', 'fare', 'party', 'butter', 'pie',
			'migraine', 'model', 'drug', 'safe', 'garden', 'strategy', 'tower', 'dinosaur',
			'rash', 'fireplace', 'captain', 'microscope', 'hamburger', 'fax', 'lodger',
			'pie', 'walking stick', 'fungus', 'shirt', 'concrete', 'rebel', 'desk', 'holiday',
		);
		$words = array_unique($words);
		$words = array_combine($words, $words);

		$add_labels = function($max_id, $type, $type_id_field) use ($db, $words) {

			$max_tags = 10;
			$table = "labels_{$type}";

			// Add defs for type
			foreach ($words as $w) {
				$db->insert('label_defs', array('label_type' => $type, 'label' => $w));
			}

			for ($id = 1; $id <= $max_id; $id++) {
				$words_copy = $words;
				$num_tags = mt_rand(1, $max_tags);
				while ($num_tags-- >= 0) {
					$words_count = count($words_copy);
					
					if (mt_rand(1,3) == 1) {
						$w = Arrays::getNthItem($words_copy, mt_rand(1,8));
					} else {
						$w = $words_copy[array_rand($words_copy)];
					}

					$db->insert($table, array($type_id_field => $id, 'label' => $w));
					unset($words_copy[$w]);
				}
			}
		};

		$add_labels(74, 'articles', 'article_id');
		$add_labels(43, 'ideas', 'idea_id');
		$add_labels(11, 'downloads', 'download_id');
		$add_labels(22, 'news', 'news_id');


		exit;
		return $this->render('DevBundle:Test:test.html.twig');
    }
}

<?php

namespace Application\DevBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity\ClientMessage;

use Orb\Util\Strings;

class TestController extends Controller
{
    public function indexAction()
    {
		$pdo = new \PDO('mysql:dbname=dphelpreal;host=localhost', 'root', 'mysquirrel');
		$dp_db = \Doctrine\DBAL\DriverManager::getConnection(array('pdo' => $pdo, 'wrapperClass' => 'Application\\DeskPRO\\DBAL\\Connection'));
		
		$db = App::getDb();
		$em = APp::getOrm();

		// Delete old KB and idea stuff
		$db->exec("TRUNCATE TABLE articles");
		$db->exec("TRUNCATE TABLE article_categories");
		$db->exec("TRUNCATE TABLE article_to_categories");
		$db->exec("TRUNCATE TABLE ideas");
		$db->exec("TRUNCATE TABLE idea_categories");

		#------------------------------
		# KB categories
		#------------------------------

		$dp_cats = $dp_db->fetchAll("SELECT id, name FROM faq_cats ORDER BY id ASC");

		$kb_cat_map = array();
		$kb_cats = array();

		$em->beginTransaction();
		foreach ($dp_cats as $dp_cat) {
			$cat = new \Application\DeskPRO\Entity\ArticleCategory();
			$cat['title'] = $dp_cat['name'];

			$em->persist($cat);
			$em->flush();

			$kb_cat_map[$dp_cat['id']] = $cat['id'];
			$kb_cats[$cat['id']] = $cat;
		}

		unset($dp_cats);

		$em->commit();

		#------------------------------
		# idea categories
		#------------------------------

		$dp_cats = $dp_db->fetchAll("SELECT id, title, parent_id FROM user_idea_categories ORDER BY parent_id ASC, display_order ASC");

		$idea_cat_map = array();
		$idea_cats = array();

		$em->beginTransaction();
		foreach ($dp_cats as $dp_cat) {
			$cat = new \Application\DeskPRO\Entity\IdeaCategory();
			$cat['title'] = $dp_cat['title'];

			if ($dp_cat['parent_id']) {
				$p_cat = $idea_cats[$dp_cat['parent_id']];
				$cat['parent'] = $p_cat;
			}

			$em->persist($cat);
			$em->flush();

			$idea_cat_map[$dp_cat['id']] = $cat['id'];
			$idea_cats[$cat['id']] = $cat;
		}

		unset($dp_cats);

		$em->commit();

		#------------------------------
		# Articles
		#------------------------------

		$agent = App::findEntity('DeskPRO:Person', 20001);
		$dp_articles = $dp_db->fetchAll("SELECT title, answer, category FROM faq_articles ORDER BY id ASC");

		$em->beginTransaction();
		foreach ($dp_articles as $dp_article) {
			$art = new \Application\DeskPRO\Entity\Article();
			$art->person = $agent;
			$art->addToCategory($kb_cats[$kb_cat_map[$dp_article['category']]]);
			$art->title = $dp_article['title'];
			$art->content = $dp_article['answer'];
			$art->markup_mode = 'html';
			$art->status = 'published';

			$em->persist($art);
		}

		unset($dp_articles);

		$em->flush();
		$em->commit();

		#------------------------------
		# Ideas
		#------------------------------

		$accepted = App::findEntity('DeskPRO:IdeaStatusCategory', 2);
		$declined = App::findEntity('DeskPRO:IdeaStatusCategory', 6);
		$completed = App::findEntity('DeskPRO:IdeaStatusCategory', 4);

		$agent = App::findEntity('DeskPRO:Person', 20001);
		$dp_ideas = $dp_db->fetchAll("SELECT category_id, title, message, status FROM user_ideas ORDER BY id ASC");

		$em->beginTransaction();
		foreach ($dp_ideas as $dp_idea) {
			$idea = new \Application\DeskPRO\Entity\Idea();
			$idea->person = $agent;
			$idea->title = $dp_idea['title'];
			$idea->setFirstCommentText($dp_idea['message']);
			$idea->category = $idea_cats[$idea_cat_map[$dp_idea['category_id']]];

			if ($dp_idea['status'] == 'new') {
				$idea['status'] = 'new';
			} elseif ($dp_idea['status'] == 'accepted') {
				$idea['status'] = 'active';
				$idea['status_category'] = $accepted;
			} elseif ($dp_idea['status'] == 'declined') {
				$idea['status'] = 'closed';
				$idea['status_category'] = $declined;
			} elseif ($dp_idea['status'] == 'completed') {
				$idea['status'] = 'closed';
				$idea['status_category'] = $completed;
			} else {
				$idea['status'] = 'new';
			}

			$em->persist($idea);
		}

		unset($dp_ideas);

		$em->flush();
		$em->commit();

		exit;
		return $this->render('DevBundle:Test:test.html.twig');
    }
}

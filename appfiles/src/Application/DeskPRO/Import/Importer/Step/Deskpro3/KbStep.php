<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage Import
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\DeskPRO\Import\Importer\Step\Deskpro3;

use Application\DeskPRO\Entity\ArticleCategory;
use Application\DeskPRO\Entity\Article;
use Application\DeskPRO\Entity\ArticleComment;

class KbStep extends AbstractDeskpro3Step
{
	public static function getTitle()
	{
		return 'Import Knowledgebase';
	}

	public function run($page = 1)
	{
		$count = $this->getOldDb()->fetchAll("SELECT COUNT(*) FROM faq_cats");
		if ($count) {
			$this->logMessage(sprintf("Importing %d knowledgebase categories", $count));

			$start_time = microtime(true);

			$this->getDb()->beginTransaction();
			try {
				$this->processCategories(0);
				$this->getDb()->commit();
			} catch (\Exception $e) {
				$this->getDb()->rollback();
				throw $e;
			}

			$end_time = microtime(true);
			$this->logMessage(sprintf("Done all categories. Took %.3f seconds.", $end_time-$start_time));
		}


		$article_ids = $this->getOldDb()->fetchAllCol("SELECT id FROM faq_articles ORDER BY id ASC");
		if ($article_ids) {
			$this->logMessage(sprintf("Importing %d knowledgebase articles", count($article_ids)));

			$start_time = microtime(true);

			$this->getDb()->beginTransaction();
			try {
				foreach ($article_ids as $aid) {
					$this->processArticle($aid);
				}
				$this->getDb()->commit();
			} catch (\Exception $e) {
				$this->getDb()->rollback();
				throw $e;
			}

			$end_time = microtime(true);
			$this->logMessage(sprintf("Done all articles. Took %.3f seconds.", $end_time-$start_time));
		}
	}


	/**
	 * Process all categories in a tree starting from by $parent_id.
	 * I.e., to process all categories start with a parent_id of 0
	 *
	 * @param $parent_id
	 */
	protected function processCategories($parent_id)
	{
		$cats = $this->getOldDb()->fetchAll("SELECT * FROM faq_cats WHERE parent = ?", array($parent_id));
		if (!$cats) {
			return;
		}

		$new_parent = null;
		if ($parent_id) {
			$new_parent = $this->getEm()->find('DeskPRO:ArticleCategory', $this->getMappedNewId('faq_cat', $parent_id));
			if (!$new_parent) {
				return;
			}
		}

		foreach ($cats as $cat) {
			#------------------------------
			# Make sure we havent already done them
			#------------------------------

			$check_exist = $this->getMappedNewId('faq_cat', $cat['id']);
			if ($check_exist) {
				$this->getLogger()->log("{$cat['id']} already mapped, skipping", 'DEBUG');
				continue;
			}

			$new_cat = new ArticleCategory();
			$new_cat->title = $cat['name'];
			$new_cat->display_order = $cat['displayorder'];
			if ($new_parent) {
				$new_cat->parent = $new_parent;
			}

			$this->getEm()->persist($new_cat);
			$this->getEm()->flush();

			$this->saveMappedId('faq_cat', $cat['id'], $new_cat->id);

			$this->processCategories($cat['id']);
		}
	}


	/**
	 * Process an article
	 */
	protected function processArticle($article_id)
	{
		$article = $this->getOldDb()->fetchAssoc("SELECT * FROM faq_articles WHERE id = ?", array($article_id));

		#------------------------------
		# Make sure we havent already done them
		#------------------------------

		$check_exist = $this->getMappedNewId('faq_article', $article['id']);
		if ($check_exist) {
			$this->getLogger()->log("{$article['id']} already mapped, skipping", 'DEBUG');
			return;
		}

		#------------------------------
		# Create it
		#------------------------------

		$new_category = $this->getEm()->find('DeskPRO:ArticleCategory', $this->getMappedNewId('faq_cat', $article['category']));
		if (!$new_category) {
			$this->logMessage("{$article['id']} has an invalid category, skipping");
			return;
		}

		$new_person = null;
		if ($article['techid_made']) {
			$new_person = $this->getEm()->find('DeskPRO:Person', $this->getMappedNewId('tech', $article['techid_made']));
		} elseif ($article['userid']) {
			$new_person = $this->getEm()->find('DeskPRO:Person', $this->getMappedNewId('user', $article['userid']));
		}
		if (!$new_person) {
			$new_person = $this->getEm()->getRepository('DeskPRO:Person')->findOneBy(array('can_admin' => true));
		}

		$new_article = new Article();
		$new_article->addToCategory($new_category);
		if ($article['published']) {
			$new_article->setStatusCode(Article::STATUS_PUBLISHED);
		} else {
			$new_article->setStatusCode(Article::STATUS_ARCHIVED);
		}
		$new_article->person = $new_person;
		$new_article->title = $article['title'];
		$new_article->content = $article['question'] . "<br /><br />" . $article['answer'];
		$new_article->date_created = new \DateTime('@' . $article['timestamp_made']);

		$this->getEm()->persist($new_article);
		$this->getEm()->flush();

		$this->saveMappedId('faq_article', $article['id'], $new_article->id);

		#------------------------------
		# Comments
		#------------------------------

		$comments = $this->getOldDb()->fetchAll("SELECT * FROM faq_comments WHERE articleid = ? AND published = 1", array($article['id']));
		foreach ($comments as $comment) {
			$new_comment = new ArticleComment();
			$new_comment->date_created = new \DateTime('@' . $comment['timestamp_created']);
			if ($comment['userid']) {
				$new_comment->person = $this->getEm()->find('DeskPRO:Person', $this->getMappedNewId('user', $comment['userid']));
			}
			if ($comment['useremail']) {
				$new_comment->email = $comment['useremail'];
			}
			$new_comment->content = $comment['comments'];

			$this->getEm()->persist($new_comment);
			$this->getEm()->flush();
		}
	}
}

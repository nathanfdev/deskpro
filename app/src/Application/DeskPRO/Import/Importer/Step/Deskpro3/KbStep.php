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
use Application\DeskPRO\Entity\ArticleRevision;
use Application\DeskPRO\Entity\ArticleComment;

class KbStep extends AbstractDeskpro3Step
{
	public static function getTitle()
	{
		return 'Import Knowledgebase';
	}

	public function countPages()
	{
		$count = $this->getOldDb()->fetchColumn("SELECT COUNT(*) FROM faq_articles");
		if (!$count) {
			return 1;
		}

		return ceil($count / 150);
	}

	/**
	 * @param $page
	 * @return array
	 */
	protected function getIdsBatch($page)
	{
		$start = $page * 150;
		$ids = $this->getOldDb()->fetchAllCol("SELECT id FROM faq_articles ORDER BY timestamp_made ASC LIMIT $start, 150");

		return $ids;
	}

	public function run($page = 1)
	{
		$batch = $this->getIdsBatch($page - 1);

		$ids = implode(',', $batch);
		if (!$ids) $ids = '0';

		$articles = $this->getOldDb()->fetchAll("SELECT * FROM faq_articles WHERE id IN ($ids)");
		$sub_start_time = microtime(true);
		$this->logMessage("-- Processing batch {$page}");

		foreach ($articles as $a) {
			$this->getDb()->beginTransaction();

			try {
				$this->processArticle($a);
				$this->getDb()->commit();
			} catch (\Exception $e) {
				$this->getDb()->rollback();
				throw $e;
			}
		}

		$sub_end_time = microtime(true);
		$this->logMessage(sprintf("-- Done. Took %.3f seconds.", $sub_end_time-$sub_start_time));
	}


	/**
	 * Process an article
	 */
	protected function processArticle($article)
	{
		$article_id = $article['id'];

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
		$new_article->date_published = new \DateTime('@' . $article['timestamp_made']);

		$this->getEm()->persist($new_article);
		$this->getEm()->flush();

		$this->saveMappedId('faq_article', $article['id'], $new_article->id);

		#------------------------------
		# Create the first revision
		#------------------------------

		$revision = new ArticleRevision();
		$revision->article = $new_article;
		$revision->title = $new_article->title;
		$revision->content = $new_article->content;

		$this->getEm()->persist($revision);
		$this->getEm()->flush();

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
			$new_comment->is_reviewed = true;

			$this->getEm()->persist($new_comment);
			$this->getEm()->flush();
		}
	}
}

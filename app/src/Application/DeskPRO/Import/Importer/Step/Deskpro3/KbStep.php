<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. http://www.deskpro.com/   |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2012, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at http://www.deskpro.com/license                           |
|                                                                          |
| By using this software, you acknowledge having read the license          |
| and agree to be bound thereby.                                           |
|                                                                          |
| Please note that DeskPRO is not free software. We release the full       |
| source code for our software because we trust our users to pay us for    |
| the huge investment in time and energy that has gone into both creating  |
| this software and supporting our customers. By providing the source code |
| we preserve our customers' ability to modify, audit and learn from our   |
| work. We have been developing DeskPRO since 2001, please help us make it |
| another decade.                                                          |
|                                                                          |
| Like the work you see? Think you could make it better? We are always     |
| looking for great developers to join us: http://www.deskpro.com/jobs/    |
|                                                                          |
| ~ Thanks, Everyone at Team DeskPRO                                       |
\**************************************************************************/

/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage Import
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
		$revision->person = $new_person;
		$revision->date_created = $new_article->date_created;

		$this->getEm()->persist($revision);
		$this->getEm()->flush();

		#------------------------------
		# Import ratings
		#------------------------------

		$ratings = $this->getDb()->fetchAll("SELECT * FROM faq_rating WHERE faqid = ?", array($article['id']));
		foreach ($ratings as $r) {

			if (!$r['timestamp']) $r['timestamp'] = time();

			$insert_rating = array();
			$insert_rating['object_type']  = 'article';
			$insert_rating['object_id']    = $new_article->id;
			$insert_rating['ip_address']   = $r['ip_address'];
			$insert_rating['date_created'] = date('Y-m-d H:i:s', $r['timestamp']);

			if ($r['rating'] == '60' || $r['rating'] == '40') {
				continue;
			}

			if ($r['rating'] == '100' || $r['rating'] == '80') {
				$insert_rating['rating'] = 1;
			} else {
				$insert_rating['rating'] = -1;
			}

			$this->getDb()->insert('ratings', $insert_rating);
		}

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

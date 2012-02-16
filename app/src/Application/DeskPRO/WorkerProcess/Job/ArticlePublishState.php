<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage WorkerProcess
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\DeskPRO\WorkerProcess\Job;

use Application\DeskPRO\App;
use Application\DeskPRO\Log\Logger;
use Application\DeskPRO\Entity\Article;

/**
 * Goes through articles with a publish date that was set in the future (publish now),
 * or an end date set (deleting or archivng now).
 */
class ArticlePublishState extends AbstractJob
{
	const DEFAULT_INTERVAL = 1800; // 30 mins

	public function run()
	{
		$count_publish = 0;
		$count_unpublish = 0;

		$article_ids = App::getDb()->fetchAllCol("
			SELECT articles.id
			FROM articles
			WHERE hidden_status = 'unpublished'
			AND date_published < '" . date('Y-m-d H:m:s') . "'
		");

		$count_publish = count($article_ids);
		$this->processPublish($article_ids);

		$article_ids = App::getDb()->fetchAllCol("
			SELECT articles.id
			FROM articles
			WHERE status = 'published'
			AND date_end < '" . date('Y-m-d H:m:s') . "'
		");

		$count_unpublish = count($article_ids);
		$this->processUnpublish($article_ids);

		if ($count_publish OR $count_unpublish) {
			$part = array();
			if ($count_publish) {
				$part[] = "Published {$count_publish} articles";
			}
			if ($count_publish) {
				$part[] = "Unpublished {$count_unpublish} articles";
			}

			$msg = implode(' and ', $part);
			$this->logStatus($msg);
		}
	}

	protected function processPublish(array $article_ids)
	{
		if (!$article_ids) return;

		$batch = 0;
		foreach ($article_ids as $article_id) {
			$article = App::findEntity('DeskPRO:Article', $article_id);

			$article['status_code'] = Article::STATUS_PUBLISHED;

			App::getOrm()->persist($article);
			if ($batch++ >= 20) {
				App::getOrm()->flush();
				App::getOrm()->clear();
			}
		}

		App::getOrm()->flush();
		App::getOrm()->clear();
	}

	protected function processUnpublish(array $article_ids)
	{
		if (!$article_ids) return;

		$batch = 0;
		foreach ($article_ids as $article_id) {
			$article = App::findEntity('DeskPRO:Article', $article_id);

			if ($article['end_action'] == Article::END_ACTION_ARCHIVE) {
				$article['status_code'] = Article::STATUS_ARCHIVED;
			} else {
				$article['status_code'] = Article::STATUS_HIDDEN . '.' . Article::HIDDEN_STATUS_DELETED;
			}

			App::getOrm()->persist($article);
			if ($batch++ >= 20) {
				App::getOrm()->flush();
				App::getOrm()->clear();
			}
		}

		App::getOrm()->flush();
		App::getOrm()->clear();
	}
}
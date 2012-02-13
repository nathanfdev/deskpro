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

use Application\DeskPRO\Entity\News;
use Application\DeskPRO\Entity\NewsCategory;

class UserNewsStep extends AbstractDeskpro3Step
{
	/**
	 * @var \Application\DeskPRO\Entity\NewsCategory
	 */
	protected $category;

	public static function getTitle()
	{
		return 'Import User News';
	}

	public function run($page = 1)
	{
		// If there arent any news besides the default, delete default data
		$default_check = $this->getDb()->fetchColumn("SELECT id FROM news ORDER BY id DESC LIMIT 1");
		if (!$default_check || $default_check == 1) {
			$this->getDb()->exec("DELETE FROM news");
			$this->getDb()->exec("DELETE FROM news_categories");
		}

		$this->category = $this->getEm()->createQuery("SELECT c FROM DeskPRO:NewsCategory c ORDER BY c.id ASC")->getOneOrNullResult();
		if (!$this->category) {
			$this->category = new NewsCategory();
			$this->category->title = "General";
			$this->getEm()->persist($this->category);
			$this->getEm()->flush();
		}

		$count = $this->getOldDb()->fetchColumn("SELECT COUNT(*) FROM news");
		if (!$count) {
			return;
		}

		$this->logMessage(sprintf("Importing %d news entries", $count));
		$news_ids = $this->getOldDb()->fetchAllCol("SELECT id FROM news ORDER BY `timestamp` DESC");

		foreach ($news_ids as $nid) {
			$this->getDb()->beginTransaction();

			try {
				$this->processNews($nid);
				$this->getDb()->commit();
			} catch (\Exception $e) {
				$this->getDb()->rollback();
				throw $e;
			}
		}
	}

	protected function processNews($news_id)
	{
		$news = $this->getOldDb()->fetchAssoc("SELECT * FROM news WHERE Id = ?", array($news_id));

		#------------------------------
		# Make sure we havent already done them
		#------------------------------

		$check_exist = $this->getMappedNewId('news', $news['id']);
		if ($check_exist) {
			$this->getLogger()->log("{$news['id']} already mapped, skipping", 'DEBUG');
			return;
		}

		#------------------------------
		# Create it
		#------------------------------

		$new_person = $this->getEm()->find('DeskPRO:Person', $this->getMappedNewId('tech', $news['techid']));

		$new_news = new News();
		$new_news->category = $this->category;
		if ($news['logged_out']) {
			$new_news->setStatusCode(News::STATUS_PUBLISHED);
		} else {
			$new_news->setStatusCode(News::STATUS_ARCHIVED);
		}
		$new_news->date_created = new \DateTime('@' . $news['timestamp']);
		$new_news->person = $new_person;
		$new_news->title = $news['title'];
		$new_news->content = $news['details'];

		$this->getEm()->persist($new_news);
		$this->getEm()->flush();

		$this->saveMappedId('news', $news['id'], $new_news->id);
	}
}

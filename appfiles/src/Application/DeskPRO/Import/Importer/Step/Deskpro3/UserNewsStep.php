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

	public function run()
	{
		$this->category = $this->getEm()->createQuery("SELECT c FROM DeskPRO:NewsCategory c ORDER BY id ASC")->getOneOrNullResult();
		if (!$this->category) {
			return;
		}

		$count = $this->getOldDb()->fetchColumn("SELECT COUNT(*) FROM news");
		if (!$count) {
			return;
		}

		$this->logMessage(sprintf("Importing %d news entries", $count));
		$news_ids = $this->getOldDb()->fetchAllCol("SELECT id FROM news ORDER BY id DESC");

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
		$new_news->date_created = new \DateTime('@' . $news['timestamp']);
		$new_news->person = $new_person;
		$new_news->title = $news['title'];
		$new_news->content = $news['details'];

		$this->getEm()->persist($new_news);
		$this->getEm()->flush();

		$this->saveMappedId('news', $news['id'], $new_news->id);
	}
}

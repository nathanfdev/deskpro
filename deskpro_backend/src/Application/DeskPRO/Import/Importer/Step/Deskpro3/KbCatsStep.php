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

class KbCatsStep extends AbstractDeskpro3Step
{
	public static function getTitle()
	{
		return 'Import Knowledgebase Categories';
	}

	public function run($page = 1)
	{
		$count = $this->getOldDb()->fetchColumn("SELECT COUNT(*) FROM faq_cats");
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
}

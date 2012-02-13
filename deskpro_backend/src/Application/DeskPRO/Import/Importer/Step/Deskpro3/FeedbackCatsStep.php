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

use Application\DeskPRO\Entity\FeedbackCategory;
use Application\DeskPRO\Entity\Feedback;
use Application\DeskPRO\Entity\FeedbackComment;

class FeedbackCatsStep extends AbstractDeskpro3Step
{
	public static function getTitle()
	{
		return 'Import Idea Categories';
	}

	public function run($page = 1)
	{
		// If there arent any ideas besides the default, delete default data
		$default_check = $this->getDb()->fetchColumn("SELECT id FROM feedback ORDER BY id DESC LIMIT 1");
		if (!$default_check || $default_check == 1) {
			$this->getDb()->exec("DELETE FROM feedback");
			$this->getDb()->exec("DELETE FROM feedback_categories");
		}

		$count = $this->getOldDb()->fetchColumn("SELECT COUNT(*) FROM user_idea_categories");
		if ($count) {
			$this->logMessage(sprintf("Importing %d feedback categories", $count));

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


	protected function processCategories($parent_id)
	{
		if ($parent_id) {
			$cats = $this->getOldDb()->fetchAll("SELECT * FROM user_idea_categories WHERE parent_id = ?", array($parent_id));
		} else {
			$cats = $this->getOldDb()->fetchAll("SELECT * FROM user_idea_categories WHERE parent_id IS NULL");
		}
		if (!$cats) {
			return;
		}

		$new_parent = null;
		if ($parent_id) {
			$new_parent = $this->getEm()->find('DeskPRO:FeedbackCategory', $this->getMappedNewId('feedback_cat', $parent_id));
			if (!$new_parent) {
				return;
			}
		}

		foreach ($cats as $cat) {
			#------------------------------
			# Make sure we havent already done them
			#------------------------------

			$check_exist = $this->getMappedNewId('feedback_cat', $cat['id']);
			if ($check_exist) {
				$this->getLogger()->log("{$cat['id']} already mapped, skipping", 'DEBUG');
				continue;
			}

			#------------------------------
			# Create it
			#------------------------------

			$new_cat = new FeedbackCategory();
			$new_cat->title = $cat['title'];
			$new_cat->display_order = $cat['display_order'];
			if ($new_parent) {
				$new_cat->parent = $new_parent;
			}

			$this->getEm()->persist($new_cat);
			$this->getEm()->flush();

			$this->saveMappedId('feedback_cat', $cat['id'], $new_cat->id);

			// Process any subcats
			$this->processCategories($cat['id']);
		}
	}
}

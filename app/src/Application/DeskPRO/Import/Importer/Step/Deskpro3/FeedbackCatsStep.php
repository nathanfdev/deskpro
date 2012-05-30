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
		if (!$this->importer->doesOldTableExist('user_idea_categories')) {
			return;
		}

		$count = $this->getOldDb()->fetchColumn("SELECT COUNT(*) FROM user_idea_categories");
		if ($count) {

			// Deleting the default example data because we're importing them
			$default_check = $this->getDb()->fetchColumn("SELECT id FROM feedback ORDER BY id DESC LIMIT 1");
			$default_check2 = $this->getDb()->fetchColumn("SELECT id FROM feedback_categories ORDER BY id DESC LIMIT 1");
			if (!$default_check || $default_check == 1 && (!$default_check2 || $default_check2 == 2)) {
				$this->getDb()->exec("SET FOREIGN_KEY_CHECKS = 0");
				$this->getDb()->exec("DELETE FROM feedback");
				$this->getDb()->exec("DELETE FROM feedback_categories");
				$this->getDb()->exec("SET FOREIGN_KEY_CHECKS = 0");
			}

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

		// Create the initial status categories
		foreach (array('planned' => 'Planning', 'started' => 'Started', 'review' => 'Under Review') as $type => $t) {
			$s = new \Application\DeskPRO\Entity\FeedbackStatusCategory();
			$s->status_type = 'active';
			$s->title = $t;
			$this->getEm()->persist($s);
			$this->getEm()->flush();

			$this->saveMappedId('ideas_cat_active', $type, $s->id);
		}

		foreach (array('completed' => 'Completed', 'duplidate' => 'Duplicate', 'exists' => 'Already Exists', 'declined' => 'Declined') as $type => $t) {
			$s = new \Application\DeskPRO\Entity\FeedbackStatusCategory();
			$s->status_type = 'closed';
			$s->title = $t;
			$this->getEm()->persist($s);
			$this->getEm()->flush();

			$this->saveMappedId('ideas_cat_closed', $type, $s->id);
		}
	}


	protected function processCategories($parent_id, $prefix = array())
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
			# Subcageory: We're mapping to the parent
			#------------------------------

			if ($parent_id) {
				$new_parent_cat_id = $this->getMappedNewId('feedback_cat', $parent_id);
				$this->saveMappedId('feedback_cat', $cat['id'], $new_parent_cat_id);

			#------------------------------
			# Create it
			#------------------------------

			} else {

				$prefix[] = $cat['title'];

				$new_cat = new FeedbackCategory();
				$new_cat->title = implode(' > ', $prefix);
				$new_cat->display_order = $cat['display_order'];
				if ($new_parent) {
					// Cats are single-level
					//$new_cat->parent = $new_parent;
				}

				$this->getEm()->persist($new_cat);
				$this->getEm()->flush();

				$this->getDb()->insert('feedback_category2usergroup', array(
					'category_id' => $new_cat->id,
					'usergroup_id' => 1
				));

				$this->saveMappedId('feedback_cat', $cat['id'], $new_cat->id);

				$this->db->insert('import_datastore', array(
					'typename' => 'dp3_ideacatid_' . $cat['id'],
					'data' => $new_cat->id
				));
			}

			// Process any subcats
			$this->processCategories($cat['id'], $prefix);
		}
	}
}

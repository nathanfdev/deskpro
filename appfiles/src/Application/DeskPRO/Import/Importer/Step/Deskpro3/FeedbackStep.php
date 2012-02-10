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

class FeedbackStep extends AbstractDeskpro3Step
{
	public static function getTitle()
	{
		return 'Import Feedback';
	}

	public function run($page = 1)
	{
		$count = $this->getOldDb()->fetchAll("SELECT COUNT(*) FROM user_idea_categories");
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


		$feedback_ids = $this->getOldDb()->fetchAllCol("SELECT id FROM user_ideas ORDER BY id ASC");
		if ($feedback_ids) {
			$this->logMessage(sprintf("Importing %d feedback", count($feedback_ids)));

			$start_time = microtime(true);

			$this->getDb()->beginTransaction();
			try {
				foreach ($feedback_ids as $iid) {
					$this->processFeedback($iid);
				}
				$this->getDb()->commit();
			} catch (\Exception $e) {
				$this->getDb()->rollback();
				throw $e;
			}

			$end_time = microtime(true);
			$this->logMessage(sprintf("Done all feedback. Took %.3f seconds.", $end_time-$start_time));
		}
	}

	protected function processCategories($parent_id)
	{
		$cats = $this->getOldDb()->fetchAll("SELECT * FROM user_idea_categories WHERE parent_id = ?", array($parent_id));
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


	/**
	 * Process an feedback
	 */
	protected function processFeedback($feedback_id)
	{
		$feedback = $this->getOldDb()->fetchAssoc("SELECT * FROM user_ideas WHERE id = ?", array($feedback_id));

		#------------------------------
		# Make sure we havent already done them
		#------------------------------

		$check_exist = $this->getMappedNewId('feedback', $feedback['id']);
		if ($check_exist) {
			$this->getLogger()->log("{$feedback['id']} already mapped, skipping", 'DEBUG');
			return;
		}

		#------------------------------
		# Create it
		#------------------------------

		$new_category = $this->getEm()->find('DeskPRO:FeedbackCategory', $this->getMappedNewId('feedback_cat', $feedback['category_id']));
		if (!$new_category) {
			$this->logMessage("{$feedback['id']} has an invalid category, skipping");
			return;
		}

		$new_person = null;
		if ($feedback['user_id']) {
			$new_person = $this->getEm()->find('DeskPRO:Person', $this->getMappedNewId('user', $feedback['userid']));
		}
		if (!$new_person) {
			$new_person = $this->getEm()->getRepository('DeskPRO:Person')->findOneBy(array('can_admin' => true));
		}

		$new_feedback = new Feedback();
		$new_feedback->addToCategory($new_category);
		if ($feedback['status'] == 'new') {
			$new_feedback->setStatusCode(Feedback::STATUS_NEW);
		} elseif ($feedback['status'] == 'accepted') {
			$new_feedback->setStatusCode(Feedback::STATUS_ACTIVE . '.1');
		} else {
			$new_feedback->setStatusCode(Feedback::STATUS_CLOSED . '.3');
		}

		$new_feedback->person = $new_person;
		$new_feedback->title = $feedback['title'];
		$new_feedback->title = $feedback['title'];
		$new_feedback->content = $feedback['question'] . "<br /><br />" . $feedback['answer'];
		$new_feedback->date_created = new \DateTime('@' . $feedback['timestamp_made']);

		$this->getEm()->persist($new_feedback);
		$this->getEm()->flush();

		$this->saveMappedId('feedback', $feedback['id'], $new_feedback->id);

		#------------------------------
		# Comments
		#------------------------------

		$comments = $this->getDb()->fetchAll("SELECT * FROM user_idea_comments WHERE feedback_id = ?");
		foreach ($comments as $comment) {
			$new_comment = new FeedbackComment();
			$new_comment->date_created = new \DateTime('@' . $comment['created_at']);
			if ($comment['userid']) {
				$new_comment->person = $this->getEm()->find('DeskPRO:Person', $this->getMappedNewId('user', $comment['userid']));
			} elseif ($comment['techid']) {
				$new_comment->person = $this->getEm()->find('DeskPRO:Person', $this->getMappedNewId('tech', $comment['techid']));
			}
			if ($comment['user_ip']) {
				$new_comment->ip_address = $comment['user_ip'];
			}

			$this->getEm()->persist($new_comment);
			$this->getEm()->flush();
		}
	}
}

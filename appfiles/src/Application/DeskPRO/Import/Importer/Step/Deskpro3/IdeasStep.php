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

use Application\DeskPRO\Entity\IdeaCategory;
use Application\DeskPRO\Entity\Idea;
use Application\DeskPRO\Entity\IdeaComment;

class IdeasStep extends AbstractDeskpro3Step
{
	public static function getTitle()
	{
		return 'Import Ideas';
	}

	public function run($page = 1)
	{
		$count = $this->getOldDb()->fetchAll("SELECT COUNT(*) FROM user_idea_categories");
		if ($count) {
			$this->logMessage(sprintf("Importing %d idea categories", $count));

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


		$idea_ids = $this->getOldDb()->fetchAllCol("SELECT id FROM user_ideas ORDER BY id ASC");
		if ($idea_ids) {
			$this->logMessage(sprintf("Importing %d ideas", count($idea_ids)));

			$start_time = microtime(true);

			$this->getDb()->beginTransaction();
			try {
				foreach ($idea_ids as $iid) {
					$this->processIdea($iid);
				}
				$this->getDb()->commit();
			} catch (\Exception $e) {
				$this->getDb()->rollback();
				throw $e;
			}

			$end_time = microtime(true);
			$this->logMessage(sprintf("Done all ideas. Took %.3f seconds.", $end_time-$start_time));
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
			$new_parent = $this->getEm()->find('DeskPRO:IdeaCategory', $this->getMappedNewId('idea_cat', $parent_id));
			if (!$new_parent) {
				return;
			}
		}

		foreach ($cats as $cat) {
			#------------------------------
			# Make sure we havent already done them
			#------------------------------

			$check_exist = $this->getMappedNewId('idea_cat', $cat['id']);
			if ($check_exist) {
				$this->getLogger()->log("{$cat['id']} already mapped, skipping", 'DEBUG');
				continue;
			}

			#------------------------------
			# Create it
			#------------------------------

			$new_cat = new IdeaCategory();
			$new_cat->title = $cat['title'];
			$new_cat->display_order = $cat['display_order'];
			if ($new_parent) {
				$new_cat->parent = $new_parent;
			}

			$this->getEm()->persist($new_cat);
			$this->getEm()->flush();

			$this->saveMappedId('idea_cat', $cat['id'], $new_cat->id);

			// Process any subcats
			$this->processCategories($cat['id']);
		}
	}


	/**
	 * Process an idea
	 */
	protected function processIdea($idea_id)
	{
		$idea = $this->getOldDb()->fetchAssoc("SELECT * FROM user_ideas WHERE id = ?", array($idea_id));

		#------------------------------
		# Make sure we havent already done them
		#------------------------------

		$check_exist = $this->getMappedNewId('idea', $idea['id']);
		if ($check_exist) {
			$this->getLogger()->log("{$idea['id']} already mapped, skipping", 'DEBUG');
			return;
		}

		#------------------------------
		# Create it
		#------------------------------

		$new_category = $this->getEm()->find('DeskPRO:IdeaCategory', $this->getMappedNewId('idea_cat', $idea['category_id']));
		if (!$new_category) {
			$this->logMessage("{$idea['id']} has an invalid category, skipping");
			return;
		}

		$new_person = null;
		if ($idea['user_id']) {
			$new_person = $this->getEm()->find('DeskPRO:Person', $this->getMappedNewId('user', $idea['userid']));
		}
		if (!$new_person) {
			$new_person = $this->getEm()->getRepository('DeskPRO:Person')->findOneBy(array('can_admin' => true));
		}

		$new_idea = new Idea();
		$new_idea->addToCategory($new_category);
		if ($idea['status'] == 'new') {
			$new_idea->setStatusCode(Idea::STATUS_NEW);
		} elseif ($idea['status'] == 'accepted') {
			$new_idea->setStatusCode(Idea::STATUS_ACTIVE . '.1');
		} else {
			$new_idea->setStatusCode(Idea::STATUS_CLOSED . '.3');
		}

		$new_idea->person = $new_person;
		$new_idea->title = $idea['title'];
		$new_idea->title = $idea['title'];
		$new_idea->content = $idea['question'] . "<br /><br />" . $idea['answer'];
		$new_idea->date_created = new \DateTime('@' . $idea['timestamp_made']);

		$this->getEm()->persist($new_idea);
		$this->getEm()->flush();

		$this->saveMappedId('idea', $idea['id'], $new_idea->id);

		#------------------------------
		# Comments
		#------------------------------

		$comments = $this->getDb()->fetchAll("SELECT * FROM user_idea_comments WHERE idea_id = ?");
		foreach ($comments as $comment) {
			$new_comment = new IdeaComment();
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

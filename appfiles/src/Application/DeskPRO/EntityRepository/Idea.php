<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @category Entities
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\DeskPRO\EntityRepository;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity\Person as PersonEntity;
use Doctrine\ORM\EntityRepository;

use Orb\Util\Arrays;
use Orb\Util\Strings;
use Orb\Util\Numbers;

class Idea extends EntityRepository
{
	############################################################################
	# Counters
	############################################################################

	/**
	 * Count the number of ideas that are awaiting validation
	 *
	 * @return int
	 */
	public function countAwaitingValidation()
	{
		return App::getDb()->fetchColumn("
			SELECT COUNT(*)
			FROM ideas
			WHERE hidden_status = ?
		", array('validating'));
	}


	/**
	 * Count the number of ideas that are 'active', grouped by status category as key.
	 * The key 0 will be used as the total.
	 *
	 * @return array
	 */
	public function countActiveGrouped()
	{
		return App::getDb()->fetchAllKeyValue("
			SELECT IFNULL(status_category_id, 0), COUNT(*) as count
			FROM ideas
			WHERE status = ?
			GROUP BY status_category_id WITH ROLLUP
		", array('active'));
	}

	/**
	 * Count the number of ideas that are 'active', grouped by status category as key.
	 * The key 0 will be used as the total.
	 *
	 * @return array
	 */
	public function countClosedGrouped()
	{
		return App::getDb()->fetchAllKeyValue("
			SELECT IFNULL(status_category_id, 0), COUNT(*) as count
			FROM ideas
			WHERE status = ?
			GROUP BY status_category_id WITH ROLLUP
		", array('closed'));
	}


	/**
	 * Count the number of hidden ideas, groupbed by hidden_status as key.
	 * The key 'hidden' will be used as the total.
	 *
	 * @return array
	 */
	public function countHiddenGrouped()
	{
		// We dont count validating with this number because
		// in the UI we generally show validating separately

		return App::getDb()->fetchAllKeyValue("
			SELECT IFNULL(hidden_status, 'hidden'), COUNT(*) as count
			FROM ideas
			WHERE status = ? AND hidden_status != ?
			GROUP BY hidden_status WITH ROLLUP
		", array('hidden', 'validating'));
	}


	/**
	 * Count the number of ideas that are new
	 *
	 * @return int
	 */
	public function countNew()
	{
		return App::getDb()->fetchColumn("
			SELECT COUNT(*)
			FROM ideas
			WHERE status = ?
		", array('new'));
	}

	/**
	 * Count the number of non-hidden ideas in all categories, grouped by category ID key.
	 * Each parent category has the sum of all children.
	 *
	 * @return array
	 */
	public function countAllCategoriesGrouped()
	{
		/*
		 * Note that the order by category_id ASC is important here.
		 * The tally loop after modifies the array as we go. We cant
		 * have a parents tally using a childs tally that was already incremented,
		 * that'd result in incorrect tallies.
		 * (Could just make a 2nd new array using 1st as a lookup, but this solution is easy enough)
		 */

		$counts = App::getDb()->fetchAllKeyValue("
			SELECT category_id, COUNT(*)
			FROM ideas
			WHERE status != ?
			GROUP BY category_id
			ORDER BY category_id ASC
		", array('hidden'));

		foreach ($counts as $cat_id => &$count) {
			$cat_childs = App::getEntityRepository('DeskPRO:IdeaCategory')->getIdsInTree($cat_id, false);
			if ($cat_childs) {
				foreach ($cat_childs as $child_cat_id) {
					if (isset($counts[$child_cat_id])) {
						$count += $counts[$child_cat_id];
					}
				}
			}
		}

		return $counts;
	}


	/**
	 * Count the number of ideas in a status category
	 *
	 * @param $category
	 * @return int
	 */
	public function countInCategory($category)
	{
		return App::getDb()->fetchColumn("
			SELECT COUNT(*)
			FROM ideas
			WHERE category_id = ?
		", array($category->id));
	}


	/**
	 * Count the number of ideas in a status category
	 *
	 * @param $category
	 * @return int
	 */
	public function countInStatusCategory($category)
	{
		return App::getDb()->fetchColumn("
			SELECT COUNT(*)
			FROM ideas
			WHERE status_category_id = ?
		", array($category->id));
	}


	############################################################################
	# Fetchers
	############################################################################

	public function getBySlug($slug)
	{
		$id = Strings::extractRegexMatch('#^([0-9]+)#', $slug, 1);
		if (!$id) return null;

		return $this->find($id);
	}

	/**
	 * Get a collection of ideas by ID. If $person_context
	 * is supplied, only articles that this person is able to view will be returned.
	 *
	 * @return array
	 */
	public function getByIds(array $ids, PersonEntity $person_context = null)
	{
		if (!$ids) return array();

		if ($person_context) {
			$ideas = $this->getEntityManager()->createQuery("
				SELECT i
				FROM DeskPRO:Idea i INDEX BY i.id
				WHERE i.id IN (" . implode(',', $ids) . ") AND i.status != 'hidden'
				ORDER BY i.id DESC
			")->execute();
		} else {
			$ideas = $this->getEntityManager()->createQuery("
				SELECT i
				FROM DeskPRO:Idea i INDEX BY i.id
				WHERE i.id IN (" . implode(',', $ids) . ")
				ORDER BY i.id DESC
			")->execute();
		}

		return $ideas;
	}

	public function getByResultIds(array $ids)
	{
		if (!$ids) return array();

		$unsorted_ideas = $this->getEntityManager()->createQuery("
			SELECT i
			FROM DeskPRO:Idea i INDEX BY i.id
			WHERE i.id IN (" . implode(',', $ids) . ")
			ORDER BY i.id DESC
		")->execute();

		$ideas = array();

		foreach ($ids as $id) {
			if (isset($unsorted_ideas[$id])) {
				$ideas[$id] = $unsorted_ideas[$id];
			}
		}

		return $ideas;
	}

	public function getIdeas($status, $node = false, $sort = 'id', $num = 10)
	{
		if ($sort == 'date') $sort = 'id';
		if (!in_array($sort, array('id', 'num_ratings'))) $sort = 'id';

		if ($node) {
			$node_ids = $node->getTreeIds(true);

			$ideas = $this->getEntityManager()->createQuery("
				SELECT i
				FROM DeskPRO:Idea i
				WHERE i.category IN (".implode(',', $node_ids).") AND i.status = ?1
				ORDER BY i.$sort DESC
			")->setParameter(1, $status)->setMaxResults($num)->execute();
		} else {
			$ideas = $this->getEntityManager()->createQuery("
				SELECT i
				FROM DeskPRO:Idea i
				WHERE i.status = ?1
				ORDER BY i.$sort DESC
			")->setParameter(1, $status)->setMaxResults($num)->execute();
		}

		return $ideas;
	}


	public function getNewest($status, $num = 10, $node = false)
	{
		// TODO this can be shortened by using a builder

		if (Numbers::isInteger($status)) {
			if ($node) {
				$cat_ids = $node->getTreeIds(true);
				$ideas = $this->getEntityManager()->createQuery("
					SELECT i
					FROM DeskPRO:Idea i INDEX BY i.id
					WHERE i.status_category = ?1 AND i.category IN (" . implode(',',$cat_ids) . ")
					ORDER BY i.id DESC
				")->setParameter(1, $status)->setMaxResults($num)->execute();
			} else {
				$ideas = $this->getEntityManager()->createQuery("
					SELECT i
					FROM DeskPRO:Idea i INDEX BY i.id
					WHERE i.status_category = ?1
					ORDER BY i.id DESC
				")->setParameter(1, $status)->setMaxResults($num)->execute();
			}
		} else {
			if ($node) {
				$cat_ids = $node->getTreeIds(true);
				$ideas = $this->getEntityManager()->createQuery("
					SELECT i
					FROM DeskPRO:Idea i INDEX BY i.id
					WHERE i.status = ?1 AND i.category IN (" . implode(',',$cat_ids) . ")
					ORDER BY i.id DESC
				")->setParameter(1, $status)->setMaxResults($num)->execute();
			} else {
				$ideas = $this->getEntityManager()->createQuery("
					SELECT i
					FROM DeskPRO:Idea i INDEX BY i.id
					WHERE i.status = ?1
					ORDER BY i.id DESC
				")->setParameter(1, $status)->setMaxResults($num)->execute();
			}
		}

		return $ideas;
	}
}

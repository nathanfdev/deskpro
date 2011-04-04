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

use \Application\DeskPRO\App;

use \Orb\Util\Arrays;

class TicketCategory extends \Doctrine\ORM\EntityRepository
{
	protected $_cat_hierarchy = null;
	protected $_cat_names = null;
	protected $_cat_ids = array();

	public function getCategoryIds()
	{
		$this->getCategoriesInHierarchy();

		return $this->_cat_ids;
	}

	public function getCategoriesInHierarchy()
	{
		if ($this->_cat_hierarchy !== null) return $this->_cat_hierarchy;

		$cat_info = App::getCache('common')->load('ticket_category_info');

		if ($cat_info) {
			foreach ($cat_info as $k => $v) {
				$this->$k = $v;
			}
		} else {

			$db = App::getDb();
			$cats = $db->fetchAllKeyed("
				SELECT id, parent_id, title
				FROM ticket_categories
				ORDER BY title ASC
			");

			$this->_cat_ids = array_keys($cats);

			$this->_cat_names = Arrays::flattenToIndex($cats, 'title');

			$cats = Arrays::intoHierarchy($cats, null);
			$this->_cats_hierarchy = $cats;

			App::getCache('common')->save(array(
				'_cats_hierarchy' => $this->_cats_hierarchy,
				'_cat_names' => $this->_cat_names,
				'_cat_ids' => $this->_cat_ids,
			), 'ticket_category_info', array('ticket_categories'));
		}

		return $this->_cats_hierarchy;
	}



	/**
	 * Gets the names for each cat, indexed by cat ID.
	 *
	 * @return array
	 */
	public function getCategoryNames($for_ids = null)
	{
		$this->getCategoriesInHierarchy();
		if ($for_ids === null) {
			return $this->_cat_names;
		}

		$ret = array();
		foreach ($for_ids as $id) {
			if (isset($this->_cat_names[$id])) {
				$ret[] = $this->_cat_names[$id];
			}
		}

		return $ret;
	}



		/**
		 * Gets a flat array of cat names, indexed by cat ID. Children
		 * names are separated by $sep.
		 *
		 * @return array
		 */
	public function getFullCategoryNames($sep = ' > ', $include_tops = true)
	{
		if ($sep === null) {
			$sep = ' > ';
		}
		return $this->_getFullCategoryNames(array(), $this->getCategoriesInHierarchy(), $sep, $include_tops);
	}

		protected function _getFullCategoryNames($basenames, $cats, $sep, $include_tops)
	{
		$names = array();

		foreach ($cats as $k => $cat) {
			$name = $basenames;
			$name[] = $cat['title'];

			if (!$cat['children'] OR $include_tops) {
				$names[$k] = implode($sep, $name);
			}
			if ($cat['children']) {
				$names = Arrays::mergeAssoc($names, $this->_getFullCategoryNames($name, $cat['children'], $sep, $include_tops));
			}
		}

		return $names;
	}



	/**
	 * Get an array of all children IDs for a specific parent. 0 means all ids in all cats
	 *
	 * @param int $parent_id
	 * @return array
	 */
	public function getIdsInTree($parent_id, $incude_top = true)
	{
		$ids = array();
		if ($incude_top AND $parent_id) {
			$ids[] = $parent_id;
		}

		$cats = $this->getCategoriesInHierarchy();
		if ($parent_id) {
			if (empty($cats[$parent_id]) OR empty($cats[$parent_id]['children'])) return array();
			$cats = $cats[$parent_id]['children'];
		}

		foreach ($cats as $cat) {
			$ids[] = $cat['id'];

			if ($cat['children']) {
				foreach ($cat['children'] as $childcat) {
					$ids[] = $childcat['id'];
				}
			}
		}

		return $ids;
	}



	/**
	 * Returns an array indexed by department ID whose value is an array of
	 * categories enabled for it.
	 *
	 * @return array
	 */
	public function departmentToCategoryMap()
	{
		$map = App::getDb()->fetchAllGrouped("
			SELECT id, department_id
			FROM ticket_categories
		", array(), 'department_id', null, 'id');

		// If a parent category is used in a mapping, then it maps all subcats too
		foreach ($map as $depid => &$cats) {
			$add = array();
			foreach ($cats as $catid) {
				$subids = self::getIdsInTree($catid, false);
				if ($subids) {
					$add = array_merge($add, $subids);
				}
			}

			if ($add) {
				$cats = array_merge($cats, $add);
				array_unique($cats);
			}
		}
		unset($cats);

		// Further processing. Each category applied to a top level dep
		// means all sub-deps have the same settings
		foreach (App::getEntityRepository('DeskPRO:Department')->getDepartmentsInHierarchy() as $dep) {
			if ($dep['children'] AND isset($map[$dep['id']])) {
				$dep_map = $map[$dep['id']];
				foreach ($dep['children'] as $subdep) {
					$subdep_map = $dep_map;
					if (isset($map[$subdep['id']])) {
						$subdep_map = array_merge($map[$subdep['id']], $subdep_map);
					}

					$map[$subdep['id']] = $subdep_map;
				}
			}
		}

		return $map;
	}



	/**
	 * Invalidates caches
	 */
	public function invalidateCaches()
	{
		App::getCache('common')->clean('matchingTag', array('ticket_categories'));
	}

	/**
	 * @see \Application\DeskPRO\DBAL\Logging\CacheInvalidor
	 * @param  $sql
	 * @return void
	 */
	public function invalidateFromQuery($sql)
	{
		$this->invalidateCaches();
	}
}
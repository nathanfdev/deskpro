<?php
/**
 * Orb
 *
 * @package Orb
 * @category Util
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Orb\Util;

/**
 * Utility functions that help work with hierarchies.
 *
 * Hierarchies can be anything so long as they implement an array interface.
 *
 * @static
 */
class HierarchyStructure
{
	/**
	 * @var array
	 */
	public $parent_map;

	/**
	 * @var array
	 */
	public $child_map;

	/**
	 * @var array
	 */
	public $cats;

	/**
	 * @var array
	 */
	public $roots;

	/**
	 * @var string
	 */
	public $id_key = 'id';

	/**
	 * @var string
	 */
	public $parent_key = 'parent';

	/**
	 * @var string
	 */
	public $children_key = 'children';

	public function __construct($cats)
	{
		$this->cats = $cats;
	}


	/**
	 * Get an array of id=>parent
	 *
	 * @param $cats
	 * @return array
	 */
	public function getParentMap()
	{
		if ($this->parent_map !== null) {
			return $this->parent_map;
		}

		$this->parent_map = array();

		foreach ($this->cats as $cat) {
			$this->parent_map[$cat[$this->id_key]] = $cat[$this->parent_key];
		}

		return $this->parent_map;
	}


	/**
	 * Get an array of parent IDs for a category in order (left to right, aka top to bottom)
	 *
	 * @param $id
	 * @return array
	 */
	public function getPathIds($cat)
	{
		$this->getParentMap();

		$cat_id = $cat[$this->id_key];

		$ids = array();
		while (!empty($this->parent_map[$cat_id])) {
			$cat_id = $this->_cat_parent_map[$cat_id];
			$ids[] = $cat_id;
		}

		$ids = array_reverse($ids);

		return $ids;
	}


	/**
	 * Get an array of parents for a category in order (left to right, aka top to bottom)
	 *
	 * @param $id
	 * @return array
	 */
	public function getPath($cat)
	{
		$ids = $this->getPathIds($cat);

		$cats = array();
		foreach ($ids as $id) {
			$cats[$id] = $this->cats[$id];
		}

		return $cats;
	}


	/**
	 * Get children IDs of a category
	 *
	 * @param int|\Application\DeskPRO\Entity\CategoryAbstract $category
	 * @param bool $direct Only get the immediate children?
	 * @return int[]
	 */
	public function getChildrenIds($category = null, $direct = true)
	{
		if (!isset($this->child_map[$category[$this->id_key]])) {
			return array();
		}

		$children_ids = $this->child_map[$category[$this->id_key]];
		if ($direct) {
			return $children_ids;
		}

		foreach ($children_ids as $cid) {
			$children_ids = array_merge($children_ids, $this->getChildrenIds($this->cats[$cid], false));
		}

		return $children_ids;
	}


	/**
	 * @param null $category
	 * @param bool $direct
	 * @return array
	 */
	public function getChildren($category = null, $direct = true)
	{
		$cats = array();
		foreach ($this->getChildrenIds() as $cid) {
			$cats[$cid] = $this->cats[$cid];
		}

		return $cats;
	}


	/**
	 * @param $category
	 * @return int
	 */
	public function getParentId($category)
	{
		if (!isset($this->parent_map[$category->id])) {
			return 0;
		}

		return $this->parent_map[$category->id];
	}


	/**
	 * @param $category
	 * @return mixed
	 */
	public function getParent($category)
	{
		$pid = $this->getParentId($category);
		if (!$pid) {
			return null;
		}

		return $this->cats[$pid];
	}
}

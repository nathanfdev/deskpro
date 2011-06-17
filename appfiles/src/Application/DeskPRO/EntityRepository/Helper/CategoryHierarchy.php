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

namespace Application\DeskPRO\EntityRepository\Helper;

use \Application\DeskPRO\App;
use \Doctrine\ORM\EntityRepository;
use \Doctrine\ORM\Mapping\ClassMetadata;

use \Orb\Util\Arrays;

class CategoryHierarchy
{
	/**
	 * @var \Doctrine\ORM\EntityRepository
	 */
	protected $repos;

	/**
	 * @var \Doctrine\ORM\Mapping\ClassMetadata
	 */
	protected $class;

	/**
	 * @var string
	 */
	protected $table_name;

	/**
	 * @var string
	 */
	protected $cache_tag = null;

	/**
	 * @var string
	 */
	protected $where_cond = null;

	protected $_cat_hierarchy = null;
	protected $_cat_hierarchy_flat = null;
	protected $_cat_names = null;
	protected $_cat_ids = array();

	public function __construct(EntityRepository $repos, ClassMetadata $class, $cache_tag = null)
	{
		$this->repos = $repos;
		$this->class = $class;
		$this->table_name = $class->getTableName();

		if (!$cache_tag) {
			$cache_tag = $this->table_name;
		}

		$this->cache_tag = $cache_tag;
	}

	
	/**
	 * Set the where condition when fetching categories
	 * 
	 * @param  $where_cond
	 * @return string
	 */
	public function setWhereCond($where_cond)
	{
		$this->where_cond = $where_cond;
	}


	

	public function getCategoryIds()
	{
		$this->getCategoriesInHierarchy();

		return $this->_cat_ids;
	}

	public function getCategoriesInHierarchy()
	{
		if ($this->_cat_hierarchy !== null) return $this->_cat_hierarchy;

		$cat_info = App::getCache('common')->load($this->table_name . '_category_info');

		if ($cat_info) {
			foreach ($cat_info as $k => $v) {
				$this->$k = $v;
			}
		} else {

			$db = App::getDb();
			$cats = $db->fetchAllKeyed("
				SELECT id, parent_id, title
				FROM {$this->table_name}
				" . ($this->where_cond ? "WHERE {$this->where_cond}" : '') . "
				ORDER BY display_order ASC
			");

			$this->_cat_ids = array_keys($cats);

			$this->_cat_names = Arrays::flattenToIndex($cats, 'title');

			$cats = Arrays::intoHierarchy($cats, null);
			$this->_cats_hierarchy = $cats;
			$this->_cat_hierarchy_flat = Arrays::flattenHierarchy($cats);

			App::getCache('common')->save(array(
				'_cats_hierarchy' => $this->_cats_hierarchy,
				'_cat_hierarchy_flat' => $this->_cat_hierarchy_flat,
				'_cat_names' => $this->_cat_names,
				'_cat_ids' => $this->_cat_ids,
			), $this->table_name.'_category_info', array($this->cache_tag));
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
	 * Get a flat hierarchy, where children are in the main array but have an increasing 'depth'
	 *
	 * @return array
	 */
	public function getFlatHierarchy()
	{
		$this->getCategoriesInHierarchy();
		return $this->_cat_hierarchy_flat;
	}

	

	/**
	 * Get an array of all children IDs for a specific parent. 0 means all ids in all cats
	 *
	 * Note this goes down all levels. For example, if the parent has children 3 levels deep,
	 * this will fetch ids from all levels.
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
			// Bad cat ID
			if (empty($cats[$parent_id])) {
				return array();
			}
			// No children, so either return nothing, or if $include_top it will just be this
			if (empty($cats[$parent_id]['children'])) {
				return $ids;
			}
			
			$cats = $cats[$parent_id]['children'];
		}

		// TODO this needs to handle unlimited depth,
		// and probably want to cache all this info

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
}
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

use Application\DeskPRO\App;
use Application\DeskPRO\EntityRepository\AbstractEntityRepository;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\EntityManager;

use Orb\Util\Arrays;

class CategoryHierarchy
{
	/**
	 * @var \Application\DeskPRO\EntityRepository\AbstractEntityRepository
	 */
	protected $repos;

	/**
	 * @var \Doctrine\ORM\EntityManager
	 */
	protected $em;

	/**
	 * @var \Doctrine\ORM\Mapping\ClassMetadata
	 */
	protected $class;

	/**
	 * @var string
	 */
	protected $entity_name;

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
	protected $_cat_parent_map = array();

	public function __construct(EntityManager $em, AbstractEntityRepository $repos, $entity_name, ClassMetadata $class, $cache_tag = null)
	{
		$this->repos       = $repos;
		$this->em          = $em;
		$this->class       = $class;
		$this->entity_name = $entity_name;
		$this->table_name  = $class->getTableName();

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


	/**
	 * Get all root node ids
	 *
	 * @return array
	 */
	public function getRootNodeIds()
	{
		$this->getCategoriesInHierarchy();

		$root_ids = array();

		foreach ($this->_cat_hierarchy as $c) {
			$root_ids[] = $c['id'];
		}

		return $root_ids;
	}


	/**
	 * Get all root nodes
	 *
	 * @return array
	 */
	public function getRootNodes()
	{
		$root_ids = $this->getRootNodeIds();

		if (!$root_ids) {
			return array();
		}

		return $this->repos->getByIds($root_ids);
	}


	/**
	 * Get all category IDs that exists
	 *
	 * @return array
	 */
	public function getCategoryIds()
	{
		$this->getCategoriesInHierarchy();

		return $this->_cat_ids;
	}


	/**
	 * Get a plain hierarchy array
	 *
	 * @return null
	 */
	public function getCategoriesInHierarchy($reset = false)
	{
		if (!$reset && $this->_cat_hierarchy !== null) return $this->_cat_hierarchy;

		if (!$reset) {
			$cat_info = App::getCache('common')->load($this->table_name . '_category_info');
		} else {
			$cat_info = null;
		}

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

			foreach ($cats as $c) {
				$this->_cat_parent_map[$c['id']] = $c['parent_id'] ? $c['parent_id'] : 0;
			}

			$this->_cat_ids = array_keys($cats);

			$this->_cat_names = Arrays::flattenToIndex($cats, 'title');

			$cats = Arrays::intoHierarchy($cats, null);
			$this->_cat_hierarchy = $cats;
			$this->_cat_hierarchy_flat = Arrays::flattenHierarchy($cats);

			App::getCache('common')->save(array(
				'_cat_hierarchy' => $this->_cat_hierarchy,
				'_cat_hierarchy_flat' => $this->_cat_hierarchy_flat,
				'_cat_names' => $this->_cat_names,
				'_cat_ids' => $this->_cat_ids,
				'_cat_parent_map' => $this->_cat_parent_map
			), $this->table_name.'_category_info', array($this->cache_tag));
		}

		return $this->_cat_hierarchy;
	}


	/**
	 * Get an array of child=>parent for all categories.
	 *
	 * @return array
	 */
	public function getParentMap()
	{
		return $this->_cat_parent_map;
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
	 * Get IDs of parents in order (left to right)
	 *
	 * @param $category
	 * @return array
	 */
	public function getPathIds($category)
	{
		$ids = array();

		$cat_id = is_object($category) ? $category->getId() : $category;

		while (!empty($this->_cat_parent_map[$cat_id])) {
			$cat_id = $this->_cat_parent_map[$cat_id];
			$ids[] = $cat_id;
		}

		$ids = array_reverse($ids);

		return $ids;
	}


	/**
	 * Get category entities for all parents
	 *
	 * @param $category
	 * @return array
	 */
	public function getPath($category)
	{
		$ids = $this->getPathIds($category);

		if (!$ids) {
			return array();
		}

		return $this->repos->getByIds($ids);
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
		$this->getCategoriesInHierarchy();

		// All ids if null
		if ($category === null) {
			return $this->_cat_ids;
		}

		$cat_id = is_object($category) ? $category->getId() : $category;
		$child_ids = array();

		if (!isset($this->_cat_hierarchy_flat[$cat_id])) {
			return array();
		}

		$start = false;
		$depth = null;
		foreach ($this->_cat_hierarchy_flat as $c) {
			if ($start) {
				// Once we go under the cat depth, we're no
				// longer traversing this category tree
				if ($c['depth'] <= $depth) {
					break;
				}

				// Once we get one level deeper, then we're
				// no longer direct children
				if ($direct && $c['depth'] >= $depth+2) {
					break;
				}

				$child_ids[] = $c['id'];
			} elseif ($c['id'] == $cat_id) {
				$start = true;
				$depth = $c['depth'];
			}
		}

		return $child_ids;
	}


	/**
	 * Get children IDs of a category
	 *
	 * @param int|\Application\DeskPRO\Entity\CategoryAbstract $category
	 * @param bool $direct Only get the immediate children?
	 * @return \Application\DeskPRO\Entity\CategoryAbstract[]
	 */
	public function getChildren($category = null, $direct = true)
	{
		$ids = $this->getChildrenIds($category, $direct);
		if (!$ids) {
			return array();
		}

		return $this->repos->getByIds($ids);
	}

	/**
	 * TODO: Remove this call
	 *
	 * @deprecated
	 */
	public function children($category = null, $direct = true)
	{
		return $this->getChildren($category, $direct);
	}


	/**
	 * Get an array of all cat IDs in a tree including the parent itself (optionally disabled).
	 *
	 * @param int $parent_id
	 * @return array
	 */
	public function getIdsInTree($parent_id, $incude_top = true)
	{
		$parent_id = is_object($parent_id) ? $parent_id->getId() : $parent_id;
		
		$ids = $this->getChildrenIds($parent_id);

		if ($incude_top) {
			array_unshift($ids, $parent_id);
		}

		return $ids;
	}


	/**
	 * Runs through the hierarchy to repair 'depth' and 'root' values,
	 * and updates all 'display_order' so that they are stored in
	 * real tree order.
	 *
	 * @return void
	 */
	public function repair()
	{
		$this->getCategoriesInHierarchy(true);

		$all = $this->em->createQuery("
			SELECT c
			FROM {$this->entity_name} c INDEX BY c.id
		")->execute();

		$display_order = 0;

		$current_root = null;

		$this->em->beginTransaction();

		foreach ($this->getFlatHierarchy() as $cid => $cinfo) {
			$cat = $all[$cid];

			$display_order += 10;
			$cat->display_order = $display_order;
			$cat->depth = $cinfo['depth'];

			if (!$cat->parent) {
				$current_root = $cat;
				$cat->root = null;
			} else {
				$cat->root = $current_root['id'];
			}

			$this->em->persist($cat);
		}

		$this->em->flush();
		$this->em->commit();
	}
}

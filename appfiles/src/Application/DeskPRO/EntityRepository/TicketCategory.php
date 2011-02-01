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

		return $cats;
	}



	/**
	 * Gets the names for each cat, indexed by cat ID.
	 *
	 * @return array
	 */
	public function getCategoryNames()
	{
		$this->getCategoriesInHierarchy();
		return $this->_cat_names;
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
}
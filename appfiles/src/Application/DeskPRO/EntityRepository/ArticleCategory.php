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
use \Doctrine\ORM\EntityRepository;

use \Orb\Util\Arrays;

class ArticleCategory extends EntityRepository
{
	protected $_cats = null;
	protected $_cats_hierarchy = null;

	protected function _loadCats()
	{
		if ($this->_cats !== null) return;

		$this->_cats = App::getDb()->fetchAll("
			SELECT id, parent_id, title, is_book
			FROM article_categories
			ORDER BY title ASC
		");

		$this->_cats_hierarchy = Arrays::intoHierarchy($this->_cats, 0, 'parent_id', 'children');
	}

	public function getCategoryHierarchy($find = null)
	{
		$this->_loadCats();

		if ($find === null) {
			return $this->_cats_hierarchy;
		}

		return array($this->_findInHierarchy($this->_cats_hierarchy, $find));
	}

	protected function _findInHierarchy($cats, $find)
	{
		foreach ($cats as $k => $cat) {
			if ($k == $find) return $cat;
			if (!empty($cat['children'])) {
				$found = $this->_findInHierarchy($cat['children'], $find);
				if ($found) return $found;
			}
		}

		return null;
	}

	/**
	 * Get IDs of all categories up to this many levels deep. Useful for fetching things 
	 * @param int $levels
	 * @return array
	 */
	public function getIdsToLevel($levels = 2)
	{
		$this->_loadCats();
		$ids = $this->_getIdsToLevel($this->_cats, $levels, 0);

		return $ids;
	}

	protected function _getIdsToLevel($cats, $levels, $cur_level)
	{
		$ids = array();

		foreach ($cats as $id => $cat) {
			$ids[] = $id;
			if ($cur_level < $levels AND !empty($cat['children'])) {
				$ids = array_merge($ids, $this->_getIdsToLevel($cats['children'], $levels, $cur_level+1));
			}
		}

		return $ids;
	}
}
<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @category Tickets
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\DeskPRO\People\PermissionLoader;

use \Application\DeskPRO\App;
use \Application\DeskPRO\Entity;
use \Application\DeskPRO\Entity\Person;
use \Application\DeskPRO\Entity\UsergroupPropertyPermission;

use \Orb\Util\Arrays;

/**
 * Loads general usergroup permissions likes flags and the like.
 */
class ArticleCategories extends AbstractLoader
{
	/**
	 * An array of specific categories allowed by actual database records
	 * @var array
	 */
	protected $specific_cats = array();

	/**
	 * An array of categories allowed for real, that we get by computing
	 * inheritance.
	 * @var array
	 */
	protected $allowed_cats = array();

	protected function init()
	{
		$this->specific_cats = App::getEntityRepository('DeskPRO:ArticleCategoryPermission')->getCategoriesForUsergroups($this->getUsergroupIds());

		$full = App::getEntityRepository('DeskPRO:ArticleCategory')->getFullHierarchy();
		$this->_computeTree($full);
	}

	protected function _computeTree(array $tree, $default = null)
	{
		foreach ($tree as $node) {
			$this_tree_default = false;
			if ($default OR in_array($node['id'], $this->specific_cats)) {
				$this->allowed_cats[] = $node['id'];
				$this_tree_default = true;
			}

			if ($node['children']) {
				$this->_computeTree($node['children'], $this_tree_default);
			}
		}
	}


	
	/**
	 * Is a cateogry allowed?
	 *
	 * @return bool
	 */
	public function isCategoryAllowed($id)
	{
		return in_array($id, $this->allowed_cats);
	}



	/**
	 * Get an array of specific categories allowed as defiend by the db.
	 * This is before inheritance is considered.
	 * 
	 * @return array
	 */
	public function getSpecificCategories()
	{
		return $this->specific_cats;
	}
	


	/**
	 * Get an array of all allowed categories.
	 *
	 * @return array
	 */
	public function getAllowedCategories()
	{
		return $this->allowed_cats;
	}



	/**
	 * Get an array of data we'll serialize
	 *
	 * @return array
	 */
	protected function serializeData()
	{
		return array(
			'specific_cats' => $this->specific_cats,
			'allowed_cats'  => $this->allowed_cats
		);
	}


	
	/**
	 * Initialize this object with an array of saved data
	 *
	 * @param array $data
	 */
	protected function unserializeData(array $data)
	{
		$this->specific_cats = $data['specific_cats'];
		$this->allowed_cats  = $data['allowed_cats'];
	}
}
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
 * A generic category loader
 */
abstract class BasicTreeCategoryPermission extends BasicCategoryPermission
{
	/**
	 * An array of specific categories allowed by actual database records
	 * @var array
	 */
	protected $specific_cats = array();
	
	protected function init()
	{
		$this->specific_cats = App::getEntityRepository($this->getCategoryPermissionEntity())->getCategoriesForUsergroups($this->getUsergroupIds());

		$full = App::getEntityRepository($this->getCategoryEntity())->getFullHierarchy();
		$this->_computeTree($full);

		$all_ids = App::getEntityRepository($this->getCategoryEntity())->getCategoryOptions();
		$this->disallowed_cats = array_diff($all_ids, $this->allowed_cats);
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
	 * Get an array of data we'll serialize
	 *
	 * @return array
	 */
	protected function serializeData()
	{
		$data = parent::serializeData();
		$data['specific_cats'] = $this->specific_cats;

		return $data;
	}


	/**
	 * Initialize this object with an array of saved data
	 *
	 * @param array $data
	 */
	protected function unserializeData(array $data)
	{
		parent::unserializeData($data);
		$this->specific_cats = $data['specific_cats'];
	}
}
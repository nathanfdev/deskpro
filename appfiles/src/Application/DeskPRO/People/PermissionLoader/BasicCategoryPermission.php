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
abstract class BasicCategoryPermission extends AbstractLoader implements CategoryPermissionsInterface
{
	/**
	 * An array of categories allowed for real, that we get by computing
	 * inheritance.
	 * @var array
	 */
	protected $allowed_cats = array();

	/**
	 * An array of disallowed categories
	 * @var array
	 */
	protected $disallowed_cats = array();

	abstract protected function getCategoryPermissionEntity();
	abstract protected function getCategoryEntity();

	protected function init()
	{
		$this->allowed_cats= App::getEntityRepository($this->getCategoryPermissionEntity())->getCategoriesForUsergroups($this->getUsergroupIds());

		$all_ids = array_keys(App::getEntityRepository($this->getCategoryEntity())->getCategoryOptions());
		$this->disallowed_cats = array_diff($all_ids, $this->allowed_cats);
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
	 * Get an array of all allowed categories.
	 *
	 * @return array
	 */
	public function getAllowedCategories()
	{
		return $this->allowed_cats;
	}


	/**
	 * Get an array of disallowed categories. (i.e., inverse of getDisallowedCategories)
	 *
	 * @return array
	 */
	public function getDisallowedCategories()
	{
		return $this->disallowed_cats;
	}


	/**
	 * Get an array of data we'll serialize
	 *
	 * @return array
	 */
	protected function serializeData()
	{
		return array(
			'allowed_cats'    => $this->allowed_cats,
			'disallowed_cats' => $this->disallowed_cats
		);
	}


	/**
	 * Initialize this object with an array of saved data
	 *
	 * @param array $data
	 */
	protected function unserializeData(array $data)
	{
		$this->allowed_cats     = $data['allowed_cats'];
		$this->disallowed_cats  = $data['disallowed_cats'];
	}
}
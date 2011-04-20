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
use \Application\DeskPRO\EntityRepository\Helper\CategoryHierarchy;

use \Orb\Util\Arrays;

class AbstractCategoryRepository extends \Doctrine\ORM\EntityRepository
{
	protected $_cat_helper = null;

	/**
	 * @return \Application\DeskPRO\EntityRepository\Helper\CategoryHierarchy
	 */
	public function getCategoryHelper()
	{
		if ($this->_cat_helper !== null) return $this->_cat_helper;

		$this->_cat_helper = new CategoryHierarchy($this, $this->getClassMetadata());

		return $this->_cat_helper;
	}

	/**#@+ Aliases to the helper methods */
	public function getCategoryIds()
	{
		return $this->getCategoryHelper()->getCategoryIds();
	}

	public function getCategoriesInHierarchy()
	{
		return $this->getCategoryHelper()->getCategoriesInHierarchy();
	}

	public function getCategoryNames($for_ids = null)
	{
		return $this->getCategoryHelper()->getCategoryNames($for_ids);
	}

	public function getFullCategoryNames($sep = ' > ', $include_tops = true)
	{
		return $this->getCategoryHelper()->getFullCategoryNames($sep, $include_tops);
	}

	public function getIdsInTree($parent_id, $incude_top = true)
	{
		return $this->getCategoryHelper()->getIdsInTree($parent_id, $incude_top);
	}
	/**#@-*/
}
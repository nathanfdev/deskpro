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

use Application\DeskPRO\EntityRepository\Helper\CategoryHierarchy;

use Orb\Util\Arrays;

class AbstractCategoryRepository extends AbstractEntityRepository
{
	protected $_cat_helper = null;

	/**
	 * @return \Application\DeskPRO\EntityRepository\Helper\CategoryHierarchy
	 */
	public function getCategoryHelper()
	{
		if ($this->_cat_helper !== null) return $this->_cat_helper;

		$this->_cat_helper = new CategoryHierarchy($this->getEntityManager(), $this, $this->getEntityName(), $this->getClassMetadata());

		return $this->_cat_helper;
	}

	/**
	 * Pass through to helper
	 *
	 * @param $method
	 * @param $args
	 * @return mixed
	 */
	public function __call($method, $args)
	{
		return call_user_func_array(array($this->getCategoryHelper(), $method), $args);
	}
}

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

use \Orb\Util\Arrays;

use \Application\DeskPRO\App;
use \Doctrine\ORM\EntityRepository;

class Department extends EntityRepository
{
	protected $_department_hierarchy = null;

	public function getDepartmentsInHierarchy()
	{
		if ($this->_department_hierarchy !== null) return $this->_department_hierarchy;

		$db = App::getDb();
		$departments = $db->fetchAllKeyed("
			SELECT id, parent_id, title
			FROM departments
			ORDER BY title ASC
		");

		$departments = Arrays::intoHierarchy($departments, null);
		$this->_department_hierarchy = $departments;

		return $departments;
	}

	/**
	 * Gets a flat array of department names, indexed by department ID. Children
	 * names are separated by $sep.
	 * 
	 * @return array
	 */
	public function getFlatDepartmentNames($sep = ' > ', $include_tops = true)
	{
		if ($sep === null) {
			$sep = ' > ';
		}
		return $this->_getFlatDepartmentNames(array(), $this->getDepartmentsInHierarchy(), $sep, $include_tops);
	}

	protected function _getFlatDepartmentNames($basenames, $deps, $sep, $include_tops)
	{
		$names = array();

		foreach ($deps as $dep) {
			$name = $basenames;
			$name[] = $dep['title'];

			if (!$dep['children'] OR $include_tops) {
				$names[] = implode($sep, $name);
			}
			if ($dep['children']) {
				$names = array_merge($names, $this->_getFlatDepartmentNames($name, $dep['children'], $sep, $include_tops));
			}
		}

		return $names;
	}
}
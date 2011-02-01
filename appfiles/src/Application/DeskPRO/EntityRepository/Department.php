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
	protected $_department_names = null;
	protected $_department_ids = array();

	public function getDepartmentIds()
	{
		$this->getDepartmentsInHierarchy();

		return $this->_department_ids;
	}

	public function getDepartmentsInHierarchy()
	{
		if ($this->_department_hierarchy !== null) return $this->_department_hierarchy;

		$db = App::getDb();
		$departments = $db->fetchAllKeyed("
			SELECT id, parent_id, title
			FROM departments
			ORDER BY title ASC
		");

		$this->_department_ids = array_keys($departments);

		$this->_department_names = Arrays::flattenToIndex($departments, 'title');

		$departments = Arrays::intoHierarchy($departments, null);
		$this->_department_hierarchy = $departments;

		return $departments;
	}



	/**
	 * Gets the names for each department, indexed by department ID.
	 *
	 * @return array
	 */
	public function getDepartmentNames()
	{
		$this->getDepartmentsInHierarchy();
		return $this->_department_names;
	}



	/**
	 * Gets a flat array of department names, indexed by department ID. Children
	 * names are separated by $sep.
	 *
	 * @return array
	 */
	public function getFullDepartmentNames($sep = ' > ', $include_tops = true)
	{
		if ($sep === null) {
			$sep = ' > ';
		}
		return $this->_getFullDepartmentNames(array(), $this->getDepartmentsInHierarchy(), $sep, $include_tops);
	}

	protected function _getFullDepartmentNames($basenames, $deps, $sep, $include_tops)
	{
		$names = array();

		foreach ($deps as $k => $dep) {
			$name = $basenames;
			$name[] = $dep['title'];

			if (!$dep['children'] OR $include_tops) {
				$names[$k] = implode($sep, $name);
			}
			if ($dep['children']) {
				$names = Arrays::mergeAssoc($names, $this->_getFullDepartmentNames($name, $dep['children'], $sep, $include_tops));
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

		$deps = $this->getDepartmentsInHierarchy();
		if ($parent_id) {
			$deps = $deps[$parent_id]['children'];
		}

		foreach ($deps as $dep) {
			$ids[] = $dep['id'];

			if ($dep['children']) {
				foreach ($dep['children'] as $childdep) {
					$ids[] = $childdep['id'];
				}
			}
		}

		return $ids;
	}
}
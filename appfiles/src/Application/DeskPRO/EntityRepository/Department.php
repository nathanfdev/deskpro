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

		$dep_info = App::getCache('common')->load('department_info');

		if ($dep_info) {
			foreach ($dep_info as $k => $v) {
				$this->$k = $v;
			}
		} else {
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

			App::getCache('common')->save(array(
				'_department_hierarchy' => $this->_department_hierarchy,
				'_department_names' => $this->_department_names,
				'_department_ids' => $this->_department_ids,
			), 'department_info', array('departments'));
		}

		return $this->_department_hierarchy;
	}



	/**
	 * Gets the names for each department, indexed by department ID.
	 *
	 * @return array
	 */
	public function getDepartmentNames($for_ids = null)
	{
		$this->getDepartmentsInHierarchy();

		if ($for_ids === null) {
			return $this->_department_names;
		}

		$ret = array();
		foreach ($for_ids as $id) {
			if (isset($this->_department_names[$id])) {
				$ret[] = $this->_department_names[$id];
			}
		}

		return $ret;
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
			if (empty($deps[$parent_id]) OR empty($deps[$parent_id]['children'])) return $ids; // $ids because it'll have top if requested with $include_top
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
	


	/**
	 * Invalidates caches associated with agent teams
	 */
	public function invalidateCaches()
	{
		App::getCache('common')->clean('matchingTag', array('departments'));
	}

	/**
	 * @see \Application\DeskPRO\DBAL\Logging\CacheInvalidor
	 * @param  $sql
	 * @return void
	 */
	public function invalidateFromQuery($sql)
	{
		$this->invalidateCaches();
	}
}
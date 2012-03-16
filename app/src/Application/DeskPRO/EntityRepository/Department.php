<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. http://www.deskpro.com/   |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2012, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at http://www.deskpro.com/license                           |
|                                                                          |
| By using this software, you acknowledge having read the license          |
| and agree to be bound thereby.                                           |
|                                                                          |
| Please note that DeskPRO is not free software. We release the full       |
| source code for our software because we trust our users to pay us for    |
| the huge investment in time and energy that has gone into both creating  |
| this software and supporting our customers. By providing the source code |
| we preserve our customers' ability to modify, audit and learn from our   |
| work. We have been developing DeskPRO since 2001, please help us make it |
| another decade.                                                          |
|                                                                          |
| Like the work you see? Think you could make it better? We are always     |
| looking for great developers to join us: http://www.deskpro.com/jobs/    |
|                                                                          |
| ~ Thanks, Everyone at Team DeskPRO                                       |
\**************************************************************************/

/**
 * DeskPRO
 *
 * @package DeskPRO
 * @category Entities
 */

namespace Application\DeskPRO\EntityRepository;

use Orb\Util\Arrays;

use Application\DeskPRO\App;
use \Doctrine\ORM\EntityRepository;

class Department extends AbstractEntityRepository implements Preloadable
{
	public function preload()
	{
		$this->_load();
	}

	private function _load()
	{
		static $has_loaded = false;
		if (!$has_loaded) {
			$has_loaded = true;
			$all = $this->getEntityManager()->createQuery("
				SELECT d
				FROM DeskPRO:Department d
				ORDER BY d.display_order ASC
			")->execute();

			$this->getIdentityHelper()->setCollectionFromResults('all', $all);
		}
	}


	public function findByTitle($title)
	{
		try {
			$department = $this->getEntityManager()->createQuery("
				SELECT d
				FROM DeskPRO:Department d
				WHERE d.title LIKE ?1
			")->setParameter(1, "%$title%")->getSingleResult();
		} catch (\Exception $e) {
			return null;
		}

		return $department;
	}

	public function getAll()
	{
		$this->_load();
		if (($top = $this->getIdentityHelper()->getCollection('top')) === null) {
			$top = array();
			foreach ($this->getIdentityHelper()->getCollection('all') as $d) {
				if (!$d->parent) {
					$top[] = $d;
				}
			}

			$this->getIdentityHelper()->setCollectionFromResults('top', $top);
		}

		return $top;
	}

	public function getDepartmentIds()
	{
		$this->_load();
		return $this->getIdentityHelper()->getCollectionIds('all');
	}

	public function getDepartmentsInHierarchy()
	{
		$this->_load();
		return $this->getAll();
	}



	/**
	 * Gets the names for each department, indexed by department ID.
	 *
	 * @return array
	 */
	public function getDepartmentNames($for_ids = null)
	{
		$this->_load();

		$names = array();

		foreach ($this->getIdentityHelper()->getCollection('all') as $d) {
			if ($for_ids && !in_array($d->id, $for_ids)) {
				continue;
			}

			$names[$d->id] = $d->getTitle();
		}

		return $names;
	}



	/**
	 * Gets a flat array of department names, indexed by department ID. Children
	 * names are separated by $sep.
	 *
	 * @return array
	 */
	public function getFullDepartmentNames($sep = ' > ', $include_tops = true)
	{
		$this->_load();
		if ($sep === null) {
			$sep = ' > ';
		}
		return $this->_getFullDepartmentNames(array(), $this->getDepartmentsInHierarchy(), $sep, $include_tops);
	}

	protected function _getFullDepartmentNames($basenames, $deps, $sep, $include_tops)
	{
		$names = array();

		foreach ($deps as $dep) {
			$k = $dep->getId();
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
		$this->_load();
		if (is_object($parent_id)) {
			$parent_id = $parent_id->id;
		} else if (is_array($parent_id)) {
			$ids = array();
			foreach ($parent_id as $pid) {
				$ids = array_merge($ids, $this->getIdsInTree($pid, $incude_top));
			}

			return $ids;
		}

		$ids = array();
		if ($incude_top AND $parent_id) {
			$ids[] = $parent_id;
		}

		$deps = $this->getDepartmentsInHierarchy();
		if ($parent_id) {
			if (
				empty($deps[$parent_id])
				OR
				empty($deps[$parent_id]['children'])
			) {
				return $ids; // $ids because it'll have top if requested with $include_top
			}
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
	 * Count all cats that exist
	 *
	 * @return int
	 */
	public function countAll()
	{
		$this->_load();
		return count($this->getIdentityHelper()->getCollectionIds('all'));
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

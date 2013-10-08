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
 */

namespace Application\DeskPRO\Departments;

use Application\DeskPRO\Entity\Department;
use Application\DeskPRO\Hierarchy\PreloadedHierarchy;
use Doctrine\ORM\EntityManager;

class TicketDepartments
{
	/**
	 * @var \Application\DeskPRO\ORM\EntityManager
	 */
	protected $em;

	/**
	 * @var \Application\DeskPRO\Hierarchy\PreloadedHierarchy
	 */
	protected $dep_hierarchy;

	/**
	 * @var int
	 */
	private $default_id;

	/**
	 * @var $try_default_id
	 */
	private $try_default_id;

	public function __construct(EntityManager $em)
	{
		$this->em = $em;
	}


	/**
	 * This sets the 'default department' preference.
	 * Note that setting an invalid or bogus ID here will not result in an exception.
	 *
	 * With an invalid pref, getDefaultDepartment will return the first selectable department.
	 * Use getById() to check if a department exists before calling this.
	 *
	 * @param int $dep_or_id
	 */
	public function setDefaultDepartmentPreference($dep_or_id)
	{
		if ($dep_or_id === null) {
			$this->default_id = null;
		} else if (is_object($dep_or_id)) {
			$this->default_id = $dep_or_id->id;
		} else {
			$this->default_id = intval($dep_or_id);
		}
	}


	/**
	 * @return \Application\DeskPRO\Entity\Department
	 */
	public function getDefaultDepartment()
	{
		if (!$this->default_id || !$this->getById($this->default_id) || $this->hasChildren($this->default_id)) {
			foreach ($this->getAll() as $dep) {
				if (!$this->hasChildren($dep)) {
					$this->default_id = $dep->getId();
					break;
				}
			}
		}

		return $this->getById($this->default_id);
	}


	/**
	 * Loads department data from the database
	 */
	private function preload()
	{
		if ($this->dep_hierarchy !== null) {
			return;
		}

		$deps = $this->em->getRepository('DeskPRO:Department')->getTicketDepartments();
		$this->dep_hierarchy = new PreloadedHierarchy($deps);
	}


	/**
	 * Resets this repository so the next time data is requested form it, it will
	 * be queried again.
	 */
	public function reset()
	{
		$this->dep_hierarchy = null;
	}


	/**
	 * @param int $id
	 * @return mixed Returns null when not found
	 */
	public function getById($id)
	{
		$this->preload();
		return $this->dep_hierarchy->getById($id);
	}

	/**
	 * @param array $ids
	 * @return \Application\DeskPRO\Entity\Department[]
	 */
	public function getByIds(array $ids)
	{
		$this->preload();
		return $this->dep_hierarchy->getByIds($ids);
	}

	/**
	 * @param mixed $obj_or_id
	 * @return bool
	 */
	public function isChild($obj_or_id)
	{
		$this->preload();
		return $this->dep_hierarchy->isChild($obj_or_id);
	}

	/**
	 * @param mixed $obj_or_id
	 * @return bool
	 */
	public function isRoot($obj_or_id)
	{
		$this->preload();
		return $this->dep_hierarchy->isRoot($obj_or_id);
	}

	/**
	 * @param $obj_or_id
	 * @return \Application\DeskPRO\Entity\Department[]
	 */
	public function getParent($obj_or_id)
	{
		$this->preload();
		return $this->dep_hierarchy->getParent($obj_or_id);
	}

	/**
	 * @param $obj_or_id
	 * @return null
	 */
	public function getParentId($obj_or_id)
	{
		$this->preload();
		return $this->dep_hierarchy->getParentId($obj_or_id);
	}

	/**
	 * @return array
	 */
	public function getParentPathIds($obj_or_id)
	{
		$this->preload();
		return $this->dep_hierarchy->getParentPathIds($obj_or_id);
	}

	/**
	 * @return \Application\DeskPRO\Entity\Department[]
	 */
	public function getParentPath($obj_or_id, $keyed = false)
	{
		$this->preload();
		return $this->dep_hierarchy->getParentPath($obj_or_id);
	}

	/**
	 * @param $obj_or_id
	 * @return bool
	 */
	public function hasChildren($obj_or_id)
	{
		$this->preload();
		return $this->dep_hierarchy->hasChildren($obj_or_id);
	}

	/**
	 * @return int
	 */
	public function countChildren($obj_or_id)
	{
		$this->preload();
		return $this->dep_hierarchy->countChildren($obj_or_id);
	}

	/**
	 * @param $obj_or_id
	 * @return bool
	 */
	public function getChildrenIds($obj_or_id)
	{
		$this->preload();
		return $this->dep_hierarchy->getChildrenIds($obj_or_id);
	}

	/**
	 * @return \Application\DeskPRO\Entity\Department[]
	 */
	public function getChildren($obj_or_id)
	{
		$this->preload();
		return $this->dep_hierarchy->getChildren($obj_or_id);
	}

	/**
	 * @return \Application\DeskPRO\Entity\Department[]
	 */
	public function getRoots()
	{
		$this->preload();
		return $this->dep_hierarchy->getRoots();
	}

	/**
	 * @return array
	 */
	public function getRootIds()
	{
		$this->preload();
		return $this->dep_hierarchy->getRootIds();
	}

	/**
	 * @return array
	 */
	public function getAllIds()
	{
		$this->preload();
		return $this->dep_hierarchy->getAllIds();
	}

	/**
	 * @return \Application\DeskPRO\Entity\Department[]
	 */
	public function getAll()
	{
		$this->preload();
		return $this->dep_hierarchy->getAll();
	}

	/**
	 * @return array
	 */
	public function getFlatArray()
	{
		$this->preload();
		return $this->dep_hierarchy->getFlatArray();
	}

	/**
	 * @return int
	 */
	public function count()
	{
		$this->preload();
		return $this->dep_hierarchy->count();
	}

	/**
	 * @return int
	 */
	public function countRoots()
	{
		$this->preload();
		return $this->dep_hierarchy->countRoots();
	}
}
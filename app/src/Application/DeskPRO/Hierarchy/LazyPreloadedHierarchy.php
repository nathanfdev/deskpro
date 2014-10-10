<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/  |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2014, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at https://www.deskpro.com/eula/                            |
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

namespace Application\DeskPRO\Hierarchy;

use Doctrine\ORM\EntityManager;

abstract class LazyPreloadedHierarchy
{
	/**
	 * @var \Application\DeskPRO\ORM\EntityManager
	 */
	protected $em;

	/**
	 * @var \Application\DeskPRO\Hierarchy\PreloadedHierarchy
	 */
	protected $hierarchy;

	/**
	 * @var int
	 */
	private $default_id;

	public function __construct(EntityManager $em)
	{
		$this->em = $em;
	}


	/**
	 * Loads records for the hierarchy
	 *
	 * @return array
	 */
	abstract protected function loadRecords();


	/**
	 * Loads department data from the database
	 */
	private function preload()
	{
		if ($this->hierarchy !== null) {
			return;
		}

		$this->hierarchy = new PreloadedHierarchy($this->loadRecords());
	}


	/**
	 * @return \Application\DeskPRO\Hierarchy\PreloadedHierarchy
	 */
	public function getHierarchy()
	{
		$this->preload();
		return $this->hierarchy;
	}


	/**
	 * Resets this repository so the next time data is requested form it, it will
	 * be queried again.
	 */
	public function reset()
	{
		$this->hierarchy = null;
	}


	####################################################################################################################


	/**
	 * @param int $id
	 * @return mixed Returns null when not found
	 */
	public function getById($id)
	{
		$this->preload();
		return $this->hierarchy->getById($id);
	}


	/**
	 * @param array $ids
	 * @return array
	 */
	public function getByIds(array $ids)
	{
		$this->preload();
		return $this->hierarchy->getByIds($ids);
	}

	/**
	 * @param mixed $obj_or_id
	 * @return bool
	 */
	public function isChild($obj_or_id)
	{
		$this->preload();
		return $this->hierarchy->isChild($obj_or_id);
	}

	/**
	 * @param mixed $obj_or_id
	 * @return bool
	 */
	public function isRoot($obj_or_id)
	{
		$this->preload();
		return $this->hierarchy->isRoot($obj_or_id);
	}

	/**
	 * @param $obj_or_id
	 * @return array
	 */
	public function getParent($obj_or_id)
	{
		$this->preload();
		return $this->hierarchy->getParent($obj_or_id);
	}

	/**
	 * @param $obj_or_id
	 * @return null
	 */
	public function getParentId($obj_or_id)
	{
		$this->preload();
		return $this->hierarchy->getParentId($obj_or_id);
	}

	/**
	 * @return array
	 */
	public function getParentPathIds($obj_or_id)
	{
		$this->preload();
		return $this->hierarchy->getParentPathIds($obj_or_id);
	}

	/**
	 * @return array
	 */
	public function getParentPath($obj_or_id, $keyed = false)
	{
		$this->preload();
		return $this->hierarchy->getParentPath($obj_or_id);
	}

	/**
	 * @param $obj_or_id
	 * @return bool
	 */
	public function hasChildren($obj_or_id)
	{
		$this->preload();
		return $this->hierarchy->hasChildren($obj_or_id);
	}

	/**
	 * @return int
	 */
	public function countChildren($obj_or_id)
	{
		$this->preload();
		return $this->hierarchy->countChildren($obj_or_id);
	}

	/**
	 * @param $obj_or_id
	 * @return bool
	 */
	public function getChildrenIds($obj_or_id)
	{
		$this->preload();
		return $this->hierarchy->getChildrenIds($obj_or_id);
	}

	/**
	 * @return array
	 */
	public function getChildren($obj_or_id)
	{
		$this->preload();
		return $this->hierarchy->getChildren($obj_or_id);
	}

	/**
	 * @return array
	 */
	public function getRoots()
	{
		$this->preload();
		return $this->hierarchy->getRoots();
	}

	/**
	 * @return array
	 */
	public function getRootIds()
	{
		$this->preload();
		return $this->hierarchy->getRootIds();
	}

	/**
	 * @return array
	 */
	public function getAllIds()
	{
		$this->preload();
		return $this->hierarchy->getAllIds();
	}

	/**
	 * @return array
	 */
	public function getAll()
	{
		$this->preload();
		return $this->hierarchy->getAll();
	}

	/**
	 * @return array
	 */
	public function getFlatArray()
	{
		$this->preload();
		return $this->hierarchy->getFlatArray();
	}

	/**
	 * @return int
	 */
	public function count()
	{
		$this->preload();
		return $this->hierarchy->count();
	}

	/**
	 * @return int
	 */
	public function countRoots()
	{
		$this->preload();
		return $this->hierarchy->countRoots();
	}
}
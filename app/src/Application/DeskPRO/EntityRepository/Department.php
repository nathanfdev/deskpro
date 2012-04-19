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

class Department extends AbstractCategoryRepository
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
		return $this->getCategoryHelper()->getRootNodes();
	}

	public function getDepartmentIds()
	{
		return $this->getCategoryHelper()->getCategoryIds();
	}

	public function getDepartmentsInHierarchy()
	{
		return $this->getCategoryHelper()->getRootNodes();
	}

	public function getDepartmentNames($for_ids = null)
	{
		return $this->getCategoryHelper()->getCategoryNames($for_ids);
	}

	public function getFullDepartmentNames($sep = ' > ', $include_tops = true)
	{
		return $this->getCategoryHelper()->getFullCategoryNames($sep, $include_tops);
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
}

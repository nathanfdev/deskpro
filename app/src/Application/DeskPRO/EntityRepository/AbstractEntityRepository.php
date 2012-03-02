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
use Orb\Util\Strings;

class AbstractEntityRepository extends \Doctrine\ORM\EntityRepository
{
	/**
	 * Get a collection of entities by ID
	 *
	 * @param bool $keep_order True to order the resulting array in the same order that ids are provided in $ids
	 * @return array
	 */
	public function getByIds(array $ids, $keep_order = false)
	{
		if (!$ids) return array();

		$name = $this->_entityName;
		$id = $this->_class->identifier[0];

		$results = $this->getEntityManager()->createQuery("
			SELECT o
			FROM {$name} o INDEX BY o.{$id}
			WHERE o.{$id} IN (?1)
		")->execute(array(1=> $ids));

		if ($keep_order) {
			$results = Arrays::orderIdArray($ids, $results, true);
		}

		return $results;
	}


	/**
	 * @return string
	 */
	public function getTableName()
	{
		return $this->getClassMetadata()->getTableName();
	}
}

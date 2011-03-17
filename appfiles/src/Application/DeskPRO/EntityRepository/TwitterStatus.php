<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @category Entities
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Pierre Minnieur <pm@pierre-minnieur.de>
 */

namespace Application\DeskPRO\EntityRepository;

use \Application\DeskPRO\App;

use \Doctrine\ORM\EntityRepository;

use \Orb\Util\Numbers;

class TwitterStatus extends EntityRepository
{
	/**
	 * @param array $userIds An array of TwitterUser ids
	 * @param Boolean $includeArchived (optional)
	 * @param string $sortByDate (optional)
	 * @return array
	 */
	public function findByUserIds(array $userIds, $includeArchived = false, $sortByDate = 'asc')
	{
		// check that sort by date is asc or desc
		if (!in_array(strtolower($sortByDate), array('asc', 'desc'))) {
			$sortByDate = 'asc';
		}

		$userIds = array_filter($userIds, function ($value) {
			if (Numbers::isInteger($value)) {
				return true;
			}

			return false;
		});

		if (!$userIds) {
			return array();
		}

		$query = "
			SELECT s
			FROM DeskPRO:TwitterStatus s
			WHERE s.user IN (".implode(',', $userIds).")
		";

		if (!$includeArchived) {
			$query .= " AND s.is_archived = 0 ";
		}

		$query .= "ORDER BY s.date_created ".strtoupper($sortByDate);

		$statuses = $this->getEntityManager()->createQuery($query)->execute();

		return $statuses;
	}
}

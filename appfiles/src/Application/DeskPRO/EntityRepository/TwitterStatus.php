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
	 * @return array
	 */
	public function findByUserIds(array $userIds)
	{
		$userIds = array_filter($userIds, function ($value) {
			if (Numbers::isInteger($value)) {
				return true;
			}

			return false;
		});

		if (!$userIds) {
			return array();
		}

		$statuses = $this->getEntityManager()->createQuery("
			SELECT s
			FROM DeskPRO:TwitterStatus s
			WHERE s.user IN (".implode(',', $userIds).")
			ORDER BY s.date_created DESC
		")->execute();

		return $statuses;
	}
}

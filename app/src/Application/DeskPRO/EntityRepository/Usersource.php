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
use Doctrine\ORM\EntityRepository;

class Usersource extends EntityRepository
{
	protected $usersources = null;

	public function getAllUsersources($active = true)
	{
		if ($active) {
			if ($this->usersources === null) {
				$this->usersources = $this->getEntityManager()->createQuery("
					SELECT u
					FROM DeskPRO:Usersource u INDEX BY u.id
					WHERE u.is_enabled = true
					ORDER BY u.display_order ASC
				")->execute();
			}

			return $this->usersources;
		} else {
			return $this->getEntityManager()->createQuery("
				SELECT u
				FROM DeskPRO:Usersource u INDEX BY u.id
				ORDER BY u.display_order ASC
			")->execute();
		}
	}

	public function getByType($type)
	{
		return $this->getEntityManager()->createQuery("
			SELECT u
			FROM DeskPRO:Usersource u
			WHERE u.source_type = ?1
		")->setParameter(1, $type)->setMaxResults(1)->getOneOrNullResult();
	}

	public function getUsersource($id)
	{
		if ($this->usersources === null) $this->getAllUsersources();

		return $this->usersources[$id];
	}

	public function getUsersourceIds()
	{
		if ($this->usersources === null) $this->getAllUsersources();

		return array_keys($this->usersources);
	}
}

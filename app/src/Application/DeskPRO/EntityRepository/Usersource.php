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

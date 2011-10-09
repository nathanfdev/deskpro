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

use Application\DeskPRO\App;

use \Doctrine\ORM\EntityRepository;

class PersonUsersourceAssoc extends EntityRepository
{
	/**
	 * Finds the PersonUsersourceAssoc for a given identity.
	 * If no association exists, null is returend.
	 */
	public function getIdentityAssociation($usersource, $identity)
	{
		try {
			$assoc = $this->_em->createQuery("
				SELECT f, p
				FROM DeskPRO:PersonUsersourceAssoc f
				LEFT JOIN f.person p
				WHERE f.usersource = ?1 AND f.identity = ?2
			")->setParameter(1, $usersource)->setParameter(2, $identity)->getSingleResult();
		} catch (\Doctrine\ORM\NoResultException $e) {
			return null;
		}

		return $assoc;
	}
}

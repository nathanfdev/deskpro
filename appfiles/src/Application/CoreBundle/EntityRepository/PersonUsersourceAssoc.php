<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @category Entities
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris@nadeau.ws>
 */

namespace Application\CoreBundle\EntityRepository;

use \DeskPRO\App;

use \Doctrine\ORM\EntityRepository;

class PersonUsersourceAssoc extends EntityRepository
{
	/**
	 * Finds the PersonUsersourceAssoc for a given identity.
	 * If no association exists, null is returend.
	 *
	 * @param  int         $usersource_id
	 * @param  string|int  $identity
	 * @return Application\CoreBundle\Entity\PersonUsersourceAssoc
	 */
	public function getIdentityAssociation($usersource_id, $identity)
	{
		try {
			$assoc = $this->_em->createQuery("
				SELECT f, p
				FROM CoreBundle:PersonUsersourceAssoc f
				LEFT JOIN f.person p
				WHERE f.usersource_id = ?1 AND f.identity = ?2
			")->setParameter(1, $usersource_id)->setParameter(2, $identity)->getSingleResult();
		} catch (\Doctrine\ORM\NoResultException $e) {
			return null;
		}

		return $assoc;
	}
}
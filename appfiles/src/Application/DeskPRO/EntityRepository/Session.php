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

use \Application\DeskPRO\App;

use \Application\DeskPRO\Entity;
use \Doctrine\ORM\EntityRepository;
use \Orb\Util\Util;

class Session extends EntityRepository
{
	/**
	 * @return Session
	 */
	public function getSessionFromCode($sess_code)
	{
		$session_id = Entity\Session::getIdFromCode($sess_code);
		if (!$session_id) {
			return null;
		}

		$session = $this->find($session_id);
		if (!$session OR !$session->checkSessionCode($sess_code)) {
			return null;
		}

		return $session;
	}


	/**
	 * Find an active session that is tied to a visitor.
	 * 
	 * @param  $visitor
	 * @return Session
	 */
	public function getSessionFromVisitor($visitor)
	{
		$session = $this->getEntityManager()->createQuery("
			SELECT s
			FROM DeskPRO:Session s
			WHERE s.visitor = ?1
			ORDER BY s.id DESC
		")->setParameter(1, $visitor)->setMaxResults(1)->execute();

		if (!count($session)) {
			return null;
		}

		return $session[0];
	}
}
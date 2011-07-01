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

use \Orb\Util\Numbers;

class TicketAccessCode extends EntityRepository
{
	public function findByAccessCode($access_code)
	{
		$info = Entity\TicketAccessCode::decodeAccessCode($access_code);
		if (!$info) {
			return null;
		}

		try {
			$rec = $this->getEntityManager()->createQuery("
				SELECT tac
				FROM DeskPRO:TicketAccessCode tac
				WHERE tac.id = :access_code_id AND tac.auth = :auth
			")->setParameters($info)->setMaxResults(1)->getSingleResult();
		} catch (\Doctrine\ORM\NoResultException $e) {
			return null;
		}

		return $rec;
	}

	public function findByTicketAndPerson($ticket, $person)
	{
		try {
			$rec = $this->getEntityManager()->createQuery("
				SELECT tac
				FROM DeskPRO:TicketAccessCode tac
				WHERE tac.ticket = ?1 AND tac.person = ?2
			")->setParameters(array(1=>$ticket, 2=>$person))->setMaxResults(1)->getSingleResult();

			return $rec;
		} catch (\Doctrine\ORM\NoResultException $e) {
			return null;
		}
	}
}
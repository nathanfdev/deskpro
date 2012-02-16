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

use Application\DeskPRO\Entity\Session as SessionEntity;
use Application\DeskPRO\Entity\Person as PersonEntity;
use Application\DeskPRO\Entity\Visitor as VisitorEntity;
use \Doctrine\ORM\EntityRepository;
use Orb\Util\Util;

class Session extends EntityRepository
{
	/**
	 * Checks for active sessions (with standard chat timeout) for agents
	 * that have their status to available
	 */
	public function hasAvailableAgents()
	{
		$datecut = date('Y-m-d H:m:s', time() - App::getSetting('core_chat.agent_timeout'));

		$check = App::getDb()->fetchColumn("
			SELECT COUNT(*)
			FROM sessions
			WHERE date_last >= ? AND active_status = ? AND is_person = 1
			LIMIT 1
		", array($datecut, 'available'));

		return $check;
	}


	/**
	 * Get an array of agent IDs
	 *
	 * @return array
	 */
	public function getAvailableAgentIds()
	{
		$datecut = date('Y-m-d H:m:s', time() - App::getSetting('core_chat.agent_timeout'));

		$ids = App::getDb()->fetchAllCol("
			SELECT person_id
			FROM sessions
			WHERE date_last >= ? AND active_status = ?
		", array($datecut, 'available'));

		return $ids;
	}


	/**
	 * @return Session
	 */
	public function getSessionFromCode($sess_code)
	{
		$session_id = SessionEntity::getIdFromCode($sess_code);
		if (!$session_id) {
			return null;
		}

		$session = $this->getEntityManager()->createQuery("
			SELECT session, person, pic, email, vis, org, org_pic, lang
			FROM DeskPRO:Session session

			LEFT JOIN session.person person
			LEFT JOIN session.visitor vis

			LEFT JOIN person.picture_blob pic
			LEFT JOIN person.primary_email email

			LEFT JOIN person.organization org
			LEFT JOIN org.picture_blob org_pic

			LEFT JOIN person.language lang

			WHERE session.id = :id
		")->setParameters(array('id' => $session_id))->getOneOrNullResult();

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
	public function getSessionFromVisitor(VisitorEntity $visitor)
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


	/**
	 * Get the latest active session for a particular user
	 *
	 * @param \Application\DeskPRO\Entity\Person $person
	 */
	public function getSessionForPerson(PersonEntity $person)
	{
		$datecut = date('Y-m-d H:m:s', time() - App::getSetting('core.sessions_lifetime'));

		return $this->getEntityManager()->createQuery("
			SELECT s
			FROM DeskPRO:Session s
			LEFT JOIN s.visitor v
			WHERE s.person = ?1 AND s.date_last > ?2
			ORDER BY s.id
		")->setMaxResults(1)
		  ->setParameter(1, $person)
		  ->setParameter(2, $datecut)
		  ->getOneOrNullResult();
	}
}

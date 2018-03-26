<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\EntityRepository;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity\Person as PersonEntity;
use Application\DeskPRO\Entity\Session as SessionEntity;

class Session extends AbstractEntityRepository
{
    /**
     * Checks for active sessions (with standard chat timeout) for agents
     * that have their status to available.
     */
    public function hasAvailableAgents($for_chat = false)
    {
        $datecut = date('Y-m-d H:i:s', time() - App::getSetting('core_chat.agent_timeout'));

        $check = App::getDb()->fetchColumn('
            SELECT COUNT(*)
            FROM sessions
            WHERE date_last >= ? AND active_status = ? AND is_person = 1 '.($for_chat ? ' AND is_chat_available = 1 ' : '').'
            LIMIT 1
        ', [$datecut, 'available']);

        return $check;
    }

    /**
     * Get an array of agent IDs.
     *
     * @return array
     */
    public function getAvailableAgentIds()
    {
        $datecut = date('Y-m-d H:i:s', time() - App::getSetting('core_chat.agent_timeout'));

        $ids = $this->getEntityManager()->getConnection()->fetchAllCol('
            SELECT DISTINCT(sessions.person_id)
            FROM sessions
            LEFT JOIN people ON (people.id = sessions.person_id)
            WHERE sessions.date_last >= ? AND sessions.is_chat_available = 1 AND people.is_agent = 1
        ', [$datecut]);

        if (App::getCurrentPerson() && App::getCurrentPerson()->is_agent) {
            \Orb\Util\Arrays::pushUnique($ids, App::getCurrentPerson()->getId());
        }

        return $ids;
    }

    /**
     * @return SessionEntity
     */
    public function getSessionFromCode($sess_code)
    {
        $session_id = SessionEntity::getIdFromCode($sess_code);
        if (!$session_id) {
            return;
        }

        $session = $this->find($session_id);
        if (!$session or !$session->checkSessionCode($sess_code)) {
            return;
        }

        return $session;
    }

    /**
     * Get the latest active session for a particular user.
     *
     * @param \Application\DeskPRO\Entity\Person $person
     * @param int|null                           $offset Max number of seconds old the session's last page can be (null for session lifetime)
     */
    public function getSessionForPerson(PersonEntity $person, $offset = null)
    {
        if ($offset === null) {
            $offset = App::getSetting('core.sessions_lifetime');
        }
        $datecut = date('Y-m-d H:i:s', time() - $offset);

        return $this->getEntityManager()->createQuery('
            SELECT s
            FROM DeskPRO:Session s
            WHERE s.person = ?1 AND s.date_last > ?2
            ORDER BY s.id
        ')->setMaxResults(1)
          ->setParameter(1, $person)
          ->setParameter(2, $datecut)
          ->getOneOrNullResult();
    }

    /**
     * Count online users.
     *
     * @return int
     */
    public function countOnlineUsers()
    {
        return $this->_em->getConnection()->fetchColumn('
            SELECT COUNT(DISTINCT sessions.visitor_id)
            FROM sessions
            LEFT JOIN people ON (people.id = sessions.person_id)
            WHERE sessions.date_last > ? AND sessions.is_helpdesk = 1 AND (people.id IS NULL OR people.is_agent = 0) AND sessions.is_bot = 0
        ', [date('Y-m-d H:i:s', time() - App::getSetting('core.sessions_lifetime'))]);
    }
}

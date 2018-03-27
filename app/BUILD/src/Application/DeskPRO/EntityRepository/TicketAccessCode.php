<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\EntityRepository;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity;

class TicketAccessCode extends AbstractEntityRepository
{
    public function findByAccessCode($access_code)
    {
        $info = Entity\TicketAccessCode::decodeAccessCode($access_code);
        if (!$info) {
            return;
        }

        try {
            $rec = $this->getEntityManager()->createQuery('
                SELECT tac
                FROM DeskPRO:TicketAccessCode tac
                WHERE tac.id = :access_code_id AND tac.auth = :auth
            ')->setParameters($info)->setMaxResults(1)->getSingleResult();
        } catch (\Doctrine\ORM\NoResultException $e) {
            return;
        }

        return $rec;
    }

    public function getTacArrayFromAccessCode($access_code)
    {
        $info = Entity\TicketAccessCode::decodeAccessCode($access_code);
        if (!$info) {
            return;
        }

        $tac = App::getDb()->fetchAssoc('
            SELECT *
            FROM ticket_access_codes
            WHERE id = ? AND auth = ?
        ', [$info['access_code_id'], $info['auth']]);

        if (!$tac) {
            return;
        }

        return $tac;
    }

    public function findByTicketAndPerson($ticket, $person)
    {
        if (!$person || !$person->id || !$ticket || !$ticket->id) {
            return;
        }

        try {
            $rec = $this->getEntityManager()->createQuery('
                SELECT tac
                FROM DeskPRO:TicketAccessCode tac
                WHERE tac.ticket = ?1 AND tac.person = ?2
            ')->setParameters([1 => $ticket, 2 => $person])->setMaxResults(1)->getSingleResult();

            return $rec;
        } catch (\Doctrine\ORM\NoResultException $e) {
            return;
        }
    }
}

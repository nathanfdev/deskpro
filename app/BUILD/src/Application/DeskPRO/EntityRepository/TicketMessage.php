<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\EntityRepository;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity;
use Doctrine\ORM\NoResultException;

class TicketMessage extends AbstractEntityRepository
{
    /**
     * @param $ticket
     *
     * @return \Application\DeskPRO\Entity\TicketMessage|null
     */
    public function getLastAgentReply($ticket)
    {
        if (!($ticket instanceof Entity\Ticket)) {
            $ticket = App::getEntityRepository(Entity\Ticket::class)->find($ticket);
        }

        return $this->getEntityManager()->createQuery('
            SELECT m
            FROM DeskPRO:TicketMessage m
            LEFT JOIN m.person p
            WHERE
                m.ticket = ?1
                AND p.is_agent = 1
                AND m.is_agent_note = 0
            ORDER BY m.id DESC
        ')->setMaxResults(1)->setParameters([1 => $ticket])->getOneOrNullResult();
    }

    /**
     * Fetch the first message of a ticket.
     *
     *
     * @param int|Entity\Ticket $ticket A ticket ID or the ID of a ticket
     *
     * @throws NoResultException If there is no message. This shouldn't happen
     *                           because a ticket should always have a message. So it's quite exceptional indeed!
     *
     * @return TicketMessage
     */
    public function getFirstTicketMessage($ticket)
    {
        if (!($ticket instanceof Entity\Ticket)) {
            $ticket = App::getEntityRepository('DeskPRO:Ticket')->find($ticket);
        }

        try {
            $message = $this->getEntityManager()->createQuery('
                SELECT m
                FROM DeskPRO:TicketMessage m
                WHERE m.ticket = ?1
                ORDER BY m.id ASC
            ')->setParameter(1, $ticket)->setMaxResults(1)->getSingleResult();

            return $message;
        } catch (NoResultException $e) {
            return;
        }
    }

    /**
     * Get all messages in a ticket.
     *
     * @param int|Entity\Ticket $ticket
     * @param array             $set_options
     *
     * @return \Application\DeskPRO\Entity\TicketMessage[]
     */
    public function getTicketMessages($ticket, array $set_options = [])
    {
        $options = array_merge([
            'order'            => 'ASC',
            'order_dir'        => null,
            'limit'            => null,
            'with_notes'       => false,
            'with_attachments' => false,
            'since_id'         => 0,
            'ids'              => null,
        ], $set_options);

        // Compatibility with other repos format
        if ($options['order_dir']) {
            $options['order'] = $options['order_dir'];
        }

        $order = strtoupper($options['order']);
        if (!in_array($order, ['ASC', 'DESC'])) {
            $order = 'ASC';
        }

        $q = $this->getEntityManager()->createQueryBuilder();
        $q->from(Entity\TicketMessage::class, 'm');
        $q->select('m');
        $q->leftJoin('m.person', 'p');
        $q->where('m.ticket = :ticket');
        $q->addOrderBy('m.date_created', $order);

        if ($options['with_attachments']) {
            $q->addSelect('a');
            $q->leftJoin('m.attachments', 'a');
        }

        $params           = [];
        $params['ticket'] = $ticket;

        if (isset($options['since_id']) && $options['since_id']) {
            $q->andWhere('m.id > :since_id');
            $params['since_id'] = $options['since_id'];
        }

        if ($options['ids'] !== null) {
            if (empty($options['ids'])) {
                $ids = [0];
            } else {
                $ids = $options['ids'];
            }

            $q->andWhere('m.id IN (:ids)');
            $params['ids'] = $ids;
        }

        if (!$options['with_notes']) {
            $q->andWhere('m.is_agent_note = false');
        }

        if ($options['limit']) {
            $q->setMaxResults($options['limit']);
        }

        $q = $q->getQuery();

        $messages = $q->execute($params);

        return $messages;
    }

    /**
     * Checks the database for a duplicate message.
     *
     * Returns the TicketMessage if there was one found, or false if none found.
     *
     * @param \Application\DeskPRO\Entity\TicketMessage $message
     * @param int                                       $secs_ago
     *
     * @return bool|mixed
     */
    public function checkDupeMessage(Entity\TicketMessage $message, $ticket = null, $secs_ago = 10800 /* 3 hours */, \Orb\Log\Logger $logger = null)
    {
        if (!App::getSetting('core_tickets.enable_dupe_checking')) {
            if ($logger) {
                $logger->logDebug('core_tickets.enable_dupe_checking is disabled');
            }

            return false;
        }

        $timesnip = new \DateTime('-'.$secs_ago.' seconds');

        if ($ticket) {
            if ($logger) {
                $logger->logDebug("[EntityRepository:TicketMessage] Checking {$message['id']} for dupe in ticket {$ticket['id']} (-$secs_ago s) as person ".($message->person ? $message->person->id : 'none'));
            }
            $check_matches = $this->_em->createQuery('
                SELECT m
                FROM DeskPRO:TicketMessage m
                JOIN m.ticket t
                WHERE m.message_hash = ?0 AND m.date_created > ?1 AND m.ticket = ?2 AND m.person = ?3
                ORDER BY m.id DESC
            ')->setMaxResults(1)->setParameters([
                $message->getMessageHash(), $timesnip, $ticket, $message->getPerson(),
            ])->getResult();
        } else {
            if ($logger) {
                $logger->logDebug("[EntityRepository:TicketMessage] Checking {$message['id']} for dupes in any previous ticket (-$secs_ago s)");
            }
            $check_matches = $this->_em->createQuery('
                SELECT m
                FROM DeskPRO:TicketMessage m
                JOIN m.ticket AS t
                WHERE m.message_hash = ?0 AND m.date_created > ?1 AND t.subject = ?2 AND m.person = ?3
            ')->setParameters([
                $message->getMessageHash(), $timesnip, $message->withNewSubject, $message->getPerson(),
            ])->getResult();
        }

        $ids = [];
        foreach ($check_matches as $t) {
            $ids[] = $t->getId();
        }
        $ids = implode(', ', $ids);

        if ($logger) {
            $logger->logDebug('[EntityRepository:TicketMessage] Found '.count($check_matches)." possibles: $ids");
        }

        if (!$check_matches || !count($check_matches)) {
            return false;
        }

        foreach ($check_matches as $check) {
            $prev_message = $this->_em->createQuery('
                SELECT m
                FROM DeskPRO:TicketMessage m
                LEFT JOIN m.person p
                WHERE m.ticket = ?0 AND m.id < ?1
                ORDER BY m.id DESC
            ')->setMaxResults(1)->setParameters([$check->ticket->getId(), $check->getId()])->getOneOrNullResult();

            if ($logger) {
                $logger->logDebug('[EntityRepository:TicketMessage] Prev message is: '.($prev_message ? $prev_message->id : 'none'));
            }

            // There is no previous message, so it is a dupe
            if (!$prev_message) {
                if ($logger) {
                    $logger->logDebug("[EntityRepository:TicketMessage] {$check['id']} is a match because no prev message, dupe yes");
                }

                return $check;
            }

            if ($logger) {
                $logger->logDebug('[EntityRepository:TicketMessage] Prev message person is: '.($prev_message->person ? $prev_message->person->id : 'none'));
            }

            // The previous message is also by us, so it is a dupe
            if ($prev_message->getPersonId() == $message->getPersonId()) {
                if ($logger) {
                    $logger->logDebug("[EntityRepository:TicketMessage] {$check['id']} is a match because prev message is by us");
                }

                return $check;
            }

            if ($logger) {
                $logger->logDebug("[EntityRepository:TicketMessage] {$check['id']} is not a match");
            }
        }

        return false;
    }

    public function getDupeByMessageID($emailId)
    {
        $q = '
            SELECT ei FROM DeskPRO:TicketMessageEmailId ei
            JOIN ei.message eim
            WHERE ei.email_id = :id and eim.date_created > :date
            ORDER BY eim.date_created DESC
            ';

        $old = $this->getEntityManager()
            ->createQuery($q)
            ->setMaxResults(1)
            ->setParameter('id', $emailId)
            ->setParameter('date', new \DateTime('-60 days'))
            ->getResult();

        if ($old) {
            return reset($old);
        }

        return;
    }
}

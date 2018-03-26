<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\EntityRepository;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity\Person as PersonEntity;
use Application\DeskPRO\Entity\Ticket as TicketEntity;
use Application\DeskPRO\Entity\TicketFeedback as TicketFeedbackEntity;
use Application\DeskPRO\Entity\TicketMessage as TicketMessageEntity;
use Orb\Util\Arrays;

class TicketFeedback extends AbstractEntityRepository
{
    /** @var int */
    protected $per_page = 10;

    /**
     * Get a feedback object for a message by a given person.
     */
    public function getFeedback(TicketMessageEntity $message, PersonEntity $person, $create_if_notexist = false)
    {
        $feedback = $this->getEntityManager()->createQuery('
                SELECT f
                FROM DeskPRO:TicketFeedback f
                WHERE f.ticket_message = ?0 AND f.person = ?1
            ')->setParameter(0, $message)
              ->setParameter(1, $person)
              ->setMaxResults(1)
              ->getOneOrNullResult();

        if (!$feedback and $create_if_notexist) {
            $feedback                 = new TicketFeedbackEntity();
            $feedback->ticket         = $message->ticket;
            $feedback->ticket_message = $message;
            $feedback->person         = $person;
        }

        return $feedback;
    }

    /**
     * @param TicketEntity $ticket
     *
     * @return array
     */
    public function getFeedbackForTicket(TicketEntity $ticket)
    {
        $res = $this->getEntityManager()->createQuery('
            SELECT f
            FROM DeskPRO:TicketFeedback f
            WHERE f.ticket = ?1
        ')->setParameter(1, $ticket)->execute();

        if (!$res) {
            return [];
        }

        $res = Arrays::keyFromData($res, 'message_id');

        return $res;
    }

    /**
     * @param int $page
     *
     * @return mixed
     */
    public function getFeedbackForFeed($page)
    {
        $query = $this->getEntityManager()->createQuery('
            SELECT f
            FROM DeskPRO:TicketFeedback f
            ORDER BY f.date_created DESC')
            ->setMaxResults($this->per_page)
            ->setFirstResult($page * $this->per_page);

        return $query->execute();
    }

    /**
     * @return mixed
     */
    public function getCountForPaging()
    {
        $query = $this->getEntityManager()->createQuery('
            SELECT COUNT(f)
            FROM DeskPRO:TicketFeedback f');

        return $query->execute();
    }

    /**
     * @return float
     */
    public function getFeedbackPagesCount()
    {
        $count = $this->getCountForPaging();

        return ceil($count[0][1] / $this->per_page);
    }

    /**
     * @param PersonEntity $agent
     * @param              $date_range
     *
     * @return array
     */
    public function getFeedbackRatingsForAgent(PersonEntity $agent, $date_range)
    {
        $db     = App::getDb();
        $result = $db->fetchAll('
            SELECT tf.rating AS rating
            FROM ticket_feedback AS tf
            INNER JOIN tickets_messages AS tm
            ON tf.ticket_id = tm.id
            WHERE tm.person_id = ?
            AND tf.date_created BETWEEN ? AND ?
        ', [$agent['id'], $date_range['start'], $date_range['end']]);

        return $result;
    }

    /**
     * @return mixed
     */
    public function getFirstCreatedDate()
    {
        $db     = App::getDb();
        $result = $db->fetchColumn('SELECT MIN(date_created) FROM ticket_feedback');

        return $result;
    }
}

<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\EntityRepository;

class TicketAttachment extends AbstractEntityRepository
{
    /**
     * Get attachments for a ticket.
     *
     * @param  $ticket
     *
     * @return array
     */
    public function getTicketAttachments($ticket)
    {
        $attachments = $this->getEntityManager()->createQuery('
            SELECT a, b
            FROM DeskPRO:TicketAttachment a INDEX BY a.id
            LEFT JOIN a.blob b
            WHERE a.ticket = ?1
            ORDER BY a.id DESC
        ')->setParameter(1, $ticket)->execute();

        return $attachments;
    }

    public function getAttachmentsForMessages($messages)
    {
        if (!$messages) {
            return [];
        }

        $message_ids = [];
        foreach ($messages as $m) {
            $message_ids[] = $m['id'];
        }

        $message_ids = implode(',', $message_ids);

        $attachments = $this->getEntityManager()->createQuery("
            SELECT a, b
            FROM DeskPRO:TicketAttachment a INDEX BY a.id
            LEFT JOIN a.blob b
            WHERE a.message IN ($message_ids)
            ORDER BY a.id DESC
        ")->execute();

        return $attachments;
    }
}

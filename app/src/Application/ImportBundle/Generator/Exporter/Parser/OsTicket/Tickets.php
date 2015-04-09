<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/  |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2014, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at https://www.deskpro.com/eula/                            |
|                                                                          |
| By using this software, you acknowledge having read the license          |
| and agree to be bound thereby.                                           |
|                                                                          |
| Please note that DeskPRO is not free software. We release the full       |
| source code for our software because we trust our users to pay us for    |
| the huge investment in time and energy that has gone into both creating  |
| this software and supporting our customers. By providing the source code |
| we preserve our customers' ability to modify, audit and learn from our   |
| work. We have been developing DeskPRO since 2001, please help us make it |
| another decade.                                                          |
|                                                                          |
| Like the work you see? Think you could make it better? We are always     |
| looking for great developers to join us: http://www.deskpro.com/jobs/    |
|                                                                          |
| ~ Thanks, Everyone at Team DeskPRO                                       |
\**************************************************************************/

namespace Application\ImportBundle\Generator\Exporter\Parser\OsTicket;

use Application\DeskPRO\Entity as DeskPROEntity;
use Application\ImportBundle\Entity;
use Application\ImportBundle\Generator\Exporter\Parser\NoColumnException;

/**
 * OsTicket tickets parser.
 *
 * Class Tickets
 */
final class Tickets extends AbstractParser
{
    /**
     * @var int
     */
    private $tickets_min_id;

    /**
     * {@inheritdoc}
     */
    public function getEntityType()
    {
        return Entity\EntityInterface::TYPE_TICKET;
    }

    /**
     * Returns current tickets offset
     *
     * @return int
     */
    public function getCurrentTicketsMinId()
    {
        return $this->tickets_min_id ? : $this->getBatchConfig()->getTicketsMinId();
    }

    /**
     * {@inheritdoc}
     */
    public function getCount()
    {
        return $this->reader->getTicketsCount($this->getCurrentTicketsMinId());
    }

    /**
     * {@inheritdoc}
     */
    public function export()
    {
        $this->entities_loaded = 0;
        $collection = new Entity\Collection();

        while ($batch = $this->reader->findTickets($this->getReaderBatchSize(), $this->getCurrentTicketsMinId())) {
            foreach ($batch as $num => $ticket) {
                $this->advanceProgressBar();
                $offsetNum = $num + $this->entities_loaded;

                try {
                    $entity = $this->exportTicket($ticket);
                    if ($entity) {
                        $collection->attach($entity);
                        $this->logInfo(sprintf('Entity `%s` parsed successfully!', $entity->getDestination()));
                } else {
                        $this->logWarning(sprintf('Invalid ticket record found (Skipping): %d', $offsetNum));
                    }

                } catch (NoColumnException $e) {
                    $this->logWarning(sprintf(
                        'Invalid ticket record `%d` found (Skipping): %s',
                        $num, $e->getMessage()
                    ));
                }

                if (isset($ticket['ticket_id'])) {
                    $this->tickets_min_id = $ticket['ticket_id'];
                }
            }

            $this->entities_loaded += count($batch);
        }

        return $collection;
    }

    /**
     * Returns a ticket entity
     *
     * @param array $ticket
     * @return Entity\Ticket|null
     */
    private function exportTicket(array $ticket)
    {
        if ($this->isTicketValid($ticket)) {
                    $entity = new Entity\Ticket();
                    $entity
                ->setDestination('ticket_' . $ticket['ticket_id'])
                ->setOid($ticket['ticket_id'])
                        ->setRef($ticket['number'])
                        ->setDepartment($this->reader->findDepartmentById($ticket['dept_id']))
                        ->setPersonEmail($this->reader->findUserEmailById($ticket['user_id']))
                        ->setAgentEmail($this->reader->findUserEmailById($ticket['staff_id']))
                        ->setAgentTeam($this->reader->findUserEmailById($ticket['team_id']))
                        ->setStatus($this->getTicketStatus($ticket))
                ->setDateCreated($this->getFromStringOrCurrentDateTime($ticket['created']))
                        ->setSubject($ticket['subject'])
                ->setPriority($this->exportPriority($ticket['priority_id']));

                    $messages = $this->exportMessages($ticket['ticket_id']);
                    foreach ($messages as $message) {
                        /* @var Entity\TicketMessage $message */
                        $entity->addMessage($message);
                    }

            return $entity;
                }

        return null;
            }

    /**
     * Returns a ticket priority entity
     *
     * @param int $id
     * @return Entity\TicketPriority|null
     */
    private function exportPriority($id)
    {
        if ($id) {
            $priority = $this->reader->findTicketPriority($id);
            $entity   = new Entity\TicketPriority();
            $entity
                ->setDestination('priority')
                ->setOid($priority['priority_id'])
                ->setTitle($priority['priority_desc'])
                ->setValue($priority['priority_urgency']);

            return $entity;
        }

        return null;
    }

    /**
     * Returns a collection of the ticket messages.
     *
     * @param int $ticket_id
     *
     * @return Entity\Collection
     */
    private function exportMessages($ticket_id)
    {
        $messages   = $this->reader->findMessages($ticket_id);
        $collection = new Entity\Collection();

        foreach ($messages as $num => $message) {
            try {
                $entity = $this->exportMessage($num, $message);
                if ($entity) {
                    $collection->attach($entity);
                    $this->logInfo(sprintf('Entity `%s` parsed successfully!', $entity->getDestination()));
            } else {
                    $this->logWarning(sprintf('Invalid ticket message record found (Skipping): %d', $num));
                }

            } catch (NoColumnException $e) {
                $this->logWarning(sprintf(
                    'Invalid ticket message record `%d` found (Skipping): %s',
                    $num, $e->getMessage()
                ));
            }
        }

        return $collection;
    }

    /**
     * Returns a ticket message entity
     *
     * @param int   $num
     * @param array $message
     *
     * @return Entity\TicketMessage|null
     */
    private function exportMessage($num, array $message)
    {
        if ($this->isMessageValid($message)) {
                $entity = new Entity\TicketMessage();
                $entity
                ->setDestination('message_' . $num)
                ->setOid($num)
                    ->setPersonEmail($this->getMessagePersonEmail($message))
                ->setDateCreated($this->getFromStringOrCurrentDateTime($message['created']))
                ->setMessageHtml($message['body']);

                $attachments = $this->exportAttachments($message['id']);
                foreach ($attachments as $attachment) {
                    /* @var Entity\Attachment $attachment */
                    $entity->addAttachment($attachment);
                }

            return $entity;
        }

        return null;
    }

    /**
     * Returns a collection of the ticket message attachments.
     *
     * @param int $message_id
     *
     * @return Entity\Collection
     */
    private function exportAttachments($message_id)
    {
        $collection  = new Entity\Collection();
        $attachments = $this->reader->findMessageAttachments($message_id);

        foreach ($attachments as $num => $attachment) {
            try {
                $entity = $this->exportAttachment($num, $attachment);
                if ($entity) {
                    $collection->attach($entity);
                    $this->logInfo(sprintf('Entity `%s` parsed successfully!', $entity->getDestination()));
            } else {
                    $this->logWarning(sprintf('Invalid ticket message attachment record found (Skipping): %d', $num));
                }

            } catch (NoColumnException $e) {
                $this->logError(sprintf(
                    'Invalid ticket message attachment record `%d` found (Skipping): %s',
                    $num, $e->getMessage()
                ));
            }
        }

        return $collection;
    }

    /**
     * Returns an attachment entity
     *
     * @param int   $num
     * @param array $attachment
     *
     * @return Entity\Attachment|null
     */
    private function exportAttachment($num, array $attachment)
    {
        if ($this->isAttachmentValid($attachment)) {
                $entity = new Entity\Attachment();
                $entity
                    ->setOid($num)
                    ->setBlobData(base64_encode($this->reader->findAttachmentData($attachment['file_id'])))
                    ->setFileName($attachment['name'])
                    ->setContentType($attachment['type']);

            return $entity;
        }

        return null;
    }

    /**
     * Get ticket status by raw data.
     *
     * @param array $ticket
     *
     * @return string
     */
    private function getTicketStatus(array $ticket)
    {
        $status = DeskPROEntity\Ticket::STATUS_AWAITING_AGENT;
        if ($ticket['isanswered']) {
            $status = DeskPROEntity\Ticket::STATUS_AWAITING_USER;
        }
        if ($ticket['closed']) {
            $status = DeskPROEntity\Ticket::STATUS_RESOLVED;
        }

        return $status;
    }

    /**
     * Get ticket message person email.
     *
     * @param array $message
     *
     * @return null|string
     */
    private function getMessagePersonEmail(array $message)
    {
        $email = null;
        if ($message['thread_type'] === 'R' && $message['staff_id']) {
            $email = $this->reader->findStaffEmailById($message['staff_id']);
        } elseif ($message['thread_type'] === 'M' && $message['user_id']) {
            $email = $this->reader->findUserEmailById($message['user_id']);
        }

        return $email;
    }

    /**
     * Check if ticket has all required columns.
     *
     * @param array $ticket
     *
     * @return bool
     */
    private function isTicketValid(array $ticket)
    {
        $columns = array(
            'ticket_id',
            'number',
            'dept_id',
            'user_id',
            'staff_id',
            'team_id',
            'created',
            'subject',
            'priority_id',
            'isanswered',
            'closed',
        );

        return $this->hasRequiredColumns($ticket, $columns);
    }

    /**
     * Check if ticket message has all required columns.
     *
     * @param array $message
     *
     * @return bool
     */
    private function isMessageValid(array $message)
    {
        $columns = array(
            'id',
            'thread_type',
            'staff_id',
            'user_id',
            'created',
            'body',
        );

        return $this->hasRequiredColumns($message, $columns);
    }

    /**
     * Check if ticket message attachment has all required columns.
     *
     * @param array $attachment
     *
     * @return bool
     */
    private function isAttachmentValid(array $attachment)
    {
        $columns = array(
            'file_id',
            'name',
            'type',
        );

        return $this->hasRequiredColumns($attachment, $columns);
    }
}

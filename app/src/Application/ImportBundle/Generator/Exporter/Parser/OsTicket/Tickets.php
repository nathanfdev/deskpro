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

use Application\ImportBundle\Generator\GeneratorInterface;
use Application\ImportBundle\Entity;
use DateTime;

/**
 * Tickets os ticket parser
 *
 * Class Tickets
 * @package Application\ImportBundle\Generator\Exporter\Parser\OsTicket
 */
class Tickets extends AbstractOsTicket
{
    /**
     * {@inheritdoc}
     */
    public function getGeneratorRecordType()
    {
        return GeneratorInterface::TYPE_TICKETS;
    }

    /**
     * {@inheritdoc}
     */
    public function getCount()
    {
        return $this->os_ticket_reader->getTicketCount();
    }

    /**
     * {@inheritdoc}
     */
    public function export()
    {
        $num    = 0;
        $offset = 0;
        $collection = new Entity\Collection();

        while ($batch = $this->os_ticket_reader->findTickets($this->config->getBatchSize(), $offset)) {
            $offset += count($batch);

            foreach ($batch as $ticket) {
                $this->advanceProgressBar();

                $entity = new Entity\Ticket();
                $entity
                    ->setDestination('ticket_' . $num)
                    ->setRef(!empty($ticket['number']) ? $ticket['number'] : null)
                    ->setDepartment($this->os_ticket_reader->findDepartmentFromId($ticket['dept_id']))
                    ->setPersonEmail($this->os_ticket_reader->findUserEmailFromId($ticket['user_id']))
                    ->setAgentEmail($this->os_ticket_reader->findUserEmailFromId($ticket['staff_id']) ?: null)
                    ->setAgentTeam($this->os_ticket_reader->findUserEmailFromId($ticket['team_id']) ?: null)
                    ->setStatus($this->getTicketStatus($ticket))
                    ->setDateCreated(new DateTime($ticket['created']))
                    ->setSubject($ticket['subject'])
                    ->setPriority($ticket['priority']);

                $messages = $this->exportMessages($ticket['ticket_id']);
                foreach ($messages as $message) {
                    /** @var Entity\TicketMessage $message */
                    $entity->addMessage($message);
                }

                $this->logInfo(sprintf('%s parsed successfully!', $entity->getDestination()));
                $collection->attach($entity);

                $num++;
            }
        }

        return $collection;
    }

    /**
     * Returns a collection of the ticket messages
     *
     * @param int $ticket_id
     * @return Entity\Collection
     */
    private function exportMessages($ticket_id)
    {
        $messages   = $this->os_ticket_reader->findMessagesByTicketId($ticket_id);
        $collection = new Entity\Collection();

        foreach ($messages as $message) {
            $entity = new Entity\TicketMessage();
            $entity
                ->setPersonEmail($this->getMessagePersonEmail($message))
                ->setDateCreated(new DateTime($message['created']))
                ->setMessageText($message['body']);

            $attachments = $this->exportMessageAttachments($message['id']);
            foreach ($attachments as $attachment) {
                /** @var Entity\TicketAttachment $attachment */
                $entity->addAttachment($attachment);
            }

            $collection->attach($entity);
        }

        return $collection;
    }

    /**
     * Returns a collection of the ticket message attachments
     *
     * @param int $message_id
     * @return Entity\Collection
     */
    private function exportMessageAttachments($message_id)
    {
        $collection  = new Entity\Collection();
        $attachments = $this->os_ticket_reader->findTicketMessageAttachment($message_id);
        foreach ($attachments as $num => $attachment) {
            $file_data = $this->os_ticket_reader->getFileData($attachment['file_id']);
            $entity = new Entity\TicketAttachment();
            $entity
                ->setOid($num)
                ->setBlobData(base64_encode($file_data))
                ->setFileName($attachment['name'])
                ->setContentType($attachment['type']);

            $collection->attach($entity);
        }

        return $collection;
    }

    /**
     * Get ticket status by raw data
     *
     * @param array $ticket
     * @return string
     */
    private function getTicketStatus(array $ticket)
    {
        $status = Entity\Ticket::STATUS_AWAITING_AGENT;
        if ($ticket['isanswered']) {
            $status = Entity\Ticket::STATUS_AWAITING_USER;
        }
        if ($ticket['closed']) {
            $status = Entity\Ticket::STATUS_RESOLVED;
        }

        return $status;
    }

    /**
     * Get ticket message person email
     *
     * @param array $message
     * @return null|string
     */
    private function getMessagePersonEmail(array $message)
    {
        $email = null;
        if ($message['thread_type'] === 'R' && $message['staff_id']) {
            $email = $this->os_ticket_reader->findStaffEmailFromId($message['staff_id']);

        } elseif ($message['thread_type'] === 'M' && $message['user_id']) {
            $email = $this->os_ticket_reader->findUserEmailFromId($message['user_id']);
        }

        return $email;
    }
}

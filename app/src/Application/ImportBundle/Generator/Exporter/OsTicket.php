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

/**
* DeskPRO
*
* @package DeskPRO
*/

namespace Application\ImportBundle\Generator\Exporter;

use Application\ImportBundle\Generator\GeneratorInterface;
use Application\ImportBundle\OsTicket\OsTicketReader;
use Application\ImportBundle\OsTicket\OsTicketReaderInterface;
use Application\ImportBundle\Entity;
use DateTime;
use Exception;

/**
 * Data generator from OsTicket
 *
 * Class OsTicket
 * @author Abhinav Kumar <abhinav.kumar@deskpro.com>
 * @package Application\ImportBundle\Generator\Exporter
 */
final class OsTicket extends AbstractExporter
{
    /**
     * @var OsTicketReader
     */
    private $os_ticket_reader;

    /**
     * Constructor
     *
     * @param OsTicketReaderInterface $os_ticket_reader
     */
    public function __construct(OsTicketReaderInterface $os_ticket_reader)
    {
        $this->os_ticket_reader = $os_ticket_reader;
    }

    /**
     * {@inheritdoc}
     */
    public function getType()
    {
        return self::TYPE_OS_TICKET;
    }

    /**
     * {@inheritdoc}
     */
    public function getRecordsCountByType($type)
    {
        switch ($type) {
            case GeneratorInterface::RECORD_TYPE_PEOPLE:
                return $this->os_ticket_reader->getPeopleCount();
            case GeneratorInterface::RECORD_TYPE_TICKETS:
                return $this->os_ticket_reader->getTicketCount();
            default:
                throw new Exception('This record type `%s` is not supported', $type);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function exportRecordsByType($type)
    {
        switch ($type) {
            case GeneratorInterface::RECORD_TYPE_PEOPLE:
                return $this->exportPeople();
            case GeneratorInterface::RECORD_TYPE_TICKETS:
                return $this->exportTickets();
            default:
                throw new Exception('This record type `%s` is not supported', $type);
        }
    }

    /**
     * @return Entity\Collection
     */
    private function exportPeople()
    {
        $collection = new Entity\Collection();
        $collection
            ->merge($this->exportStaff())
            ->merge($this->exportUsers());

        $index = 1;
        foreach ($collection as $person) {
            /** @var Entity\Person $person */
            $person
                ->setDestination('person_' . $index)
                ->setOid($index);

            $collection->attach($person);
            $index++;
        }

        return $collection;
    }

    /**
     * @return Entity\Collection
     */
    private function exportStaff()
    {
        $offset     = 0;
        $collection = new Entity\Collection();

        while ($batch = $this->os_ticket_reader->findAllStaff($offset)) {
            foreach ($batch as $person) {
                $this->advanceProgressBar();

                $entity = new Entity\Person();
                $entity
                    ->setAsAgent(true)
                    ->setFirstName($person['firstname'])
                    ->setLastName($person['lastname'])
                    ->setTimezone($this->os_ticket_reader->findTimezoneFromId($person['timezone_id']))
                    ->setDateCreated(new DateTime($person['created']))
                    ->addEmail($person['email']);

                $collection->attach($entity);
                $this->logInfo(sprintf(
                    'Person `%s %s` exported successfully!',
                    $entity->getFirstName(), $entity->getLastName()
                ));
            }

            $offset += count($batch);
        }
    }

    /**
     * @return Entity\Collection
     */
    private function exportUsers()
    {
        $collection = new Entity\Collection();
        $users = $this->os_ticket_reader->findAllUser();
        foreach ($users as $person) {
            $this->advanceProgressBar();

            $entity = new Entity\Person();
            $entity
                ->setAsUser(true)
                ->setName($person['name'])
                ->setDateCreated(new DateTime($person['created']))
                ->addEmail($person['address']);

            $collection->attach($entity);
            $this->logInfo(sprintf(
                'Person `%s` exported successfully!',
                $entity->getName()
            ));
        }

        return $collection;
    }

    /**
     * @return Entity\Collection
     */
    private function exportTickets()
    {
        $index  = 1;
        $offset = 0;
        $collection = new Entity\Collection();

        while ($ticket_batch = $this->os_ticket_reader->findAllTickets($offset)) {
            foreach ($ticket_batch as $ticket) {
                $ticket_entity = new Entity\Ticket();
                $ticket_entity
                    ->setDestination('ticket_' . $index)
                    ->setRef(!empty($ticket['number']) ? $ticket['number'] : null)
                    ->setDepartment($this->os_ticket_reader->findDepartmentFromId($ticket['dept_id']))
                    ->setPerson($this->os_ticket_reader->findUserEmailFromId($ticket['user_id']))
                    ->setAgent($this->os_ticket_reader->findUserEmailFromId($ticket['staff_id']) ?: null)
                    ->setAgentTeam($this->os_ticket_reader->findUserEmailFromId($ticket['team_id']) ?: null)
                    ->setStatus($this->getTicketStatus($ticket))
                    ->setDateCreated(new DateTime($ticket['created']))
                    ->setSubject($ticket['subject'])
                    ->setPriority($ticket['priority']);

                $messages = $this->exportTicketMessages($ticket['ticket_id']);
                foreach ($messages as $message) {
                    /** @var Entity\TicketMessage $message */
                    $ticket_entity->addMessage($message);
                }

                $this->logInfo(sprintf('%s exported successfully!', $ticket_entity->getDestination()));
                $collection->attach($ticket_entity);

                $offset++;
                $index++;
                $this->advanceProgressBar();
            }
        }

        return $collection;
    }

    /**
     * @param int $ticket_id
     * @return Entity\Collection
     */
    private function exportTicketMessages($ticket_id)
    {
        $messages   = $this->os_ticket_reader->findMessagesByTicketId($ticket_id);
        $collection = new Entity\Collection();

        foreach ($messages as $message) {
            $message_entity = new Entity\TicketMessage();
            $message_entity
                ->setPersonEmail($this->getTicketMessagePersonEmail($message))
                ->setDateCreated(new DateTime($message['created']))
                ->setMessageText($message['body']);

            $attachments = $this->os_ticket_reader->findTicketMessageAttachment($message['id']);
            foreach ($attachments as $index => $attachment) {
                $file_data = $this->os_ticket_reader->getFileData($attachment['file_id']);
                $attachment_entity = new Entity\TicketAttachment();
                $attachment_entity
                    ->setOid($index)
                    ->setBlobData(base64_encode($file_data))
                    ->setFileName($attachment['name'])
                    ->setContentType($attachment['type']);

                $message_entity->addAttachment($attachment_entity);
            }

            $collection->attach($message_entity);
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
    private function getTicketMessagePersonEmail(array $message)
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

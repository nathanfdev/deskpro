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
use Application\ImportBundle\OsTicket\OsTicketReaderInterface;
use Application\ImportBundle\Entity;
use DateTime;

/**
 * Data generator from OsTicket
 *
 * @author Abhinav Kumar <abhinav.kumar@deskpro.com>
 * @package Application\ImportBundle\Generator\Exporter
 */
class OsTicket extends AbstractGeneratorExporter
{
    /**
     * @var OsTicketReaderInterface
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
        return self::GENERATOR_TYPE_OS_TICKET;
    }

    /**
     * {@inheritdoc}
     */
    public function getRecordsCountByType($type)
    {
        switch ($type) {
            case self::RECORD_TYPE_PEOPLE:
                return $this->os_ticket_reader->getPeopleCount();
            case self::RECORD_TYPE_TICKETS:
                return $this->os_ticket_reader->getTicketCount();
            default:
                throw new \Exception(sprintf('Unknown record type `%s`', $type));
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
                throw new \Exception('This record type `%s` is not supported', $type);
        }
    }

    /**
     * @return array
     */
    protected function exportPeople()
    {
        $index  = 1;
        $offset = 0;

        $collection   = array();
        $person_batch = $this->findAllStaff($offset);

        while ($person_batch) {
            foreach ($person_batch as $person) {
                $this->advanceProgressBar();

                $person_entity = new Entity\Person();
                $person_entity
                    ->setOid($index)
                    ->setAsAgent(true)
                    ->setFirstName($person['firstname'])
                    ->setLastName($person['lastname'])
                    ->setTimezone($this->findTimezoneFromId($person['timezone_id']))
                    ->setDateCreated(new DateTime($person['created']))
                    ->addEmail($person['email']);

                $collection[] = $person_entity;

//                $file_name = $this->getExportPeopleOutputPath() . 'person' . $index . '.json';
//                $this->logInfo(sprintf('%s exported successfully!', $file_name));

                $index++;
                $offset++;
            }

            unset($person);
            $person_batch = $this->findAllStaff($offset);
        }

        foreach ($this->findAllUser() as $person) {
            $this->advanceProgressBar();

            $person_entity = new Entity\Person();
            $person_entity
                ->setOid($index)
                ->setAsUser(true)
                ->setName($person['name'])
                ->setDateCreated(new DateTime($person['created']))
                ->addEmail($person['address']);

            $collection[] = $person_entity;

//            $file_name = $this->getExportPeopleOutputPath() . 'person' . $index . '.json';
//            $this->logInfo(sprintf('%s exported successfully!', $file_name));

            $index++;
        }

        return $collection;
    }

    /**
     * @return array
     */
    protected function exportTickets()
    {
        $index = 1;
        $offset = 0;

        $collection   = array();
        $ticket_batch = $this->findAllTickets($offset);

        while ($ticket_batch = $this->findAllTickets($offset)) {
            foreach ($ticket_batch as $ticket) {
                $ticket_entity = new Entity\Ticket();
                $ticket_entity
                    ->setRef(!empty($ticket['number']) ? $ticket['number'] : null)
                    ->setDepartment($this->findDepartmentFromId($ticket['dept_id']))
                    ->setPerson($this->findUserEmailFromId($ticket['user_id']))
                    ->setAgent($this->findUserEmailFromId($ticket['staff_id']) ?: null)
                    ->setAgentTeam($this->findUserEmailFromId($ticket['team_id']) ?: null)
                    ->setStatus($ticket['closed'] ? 'resolved' : $ticket['isanswered'] ? 'awaiting_user' : 'awaiting_agent')
                    ->setDateCreated(new DateTime($ticket['created']))
                    ->setSubject($ticket['subject'])
                    ->setPriority($ticket['priority']);

                $ticket_messages = $this->findMessageThreadFromId($ticket['ticket_id']);
                foreach ($ticket_messages as $message_thread) {
                    $person_email = null;
                    if ($message_thread['thread_type'] === 'R' && $message_thread['staff_id']) {
                        $person_email = $this->findStaffEmailFromId($message_thread['staff_id']);

                    } elseif ($message_thread['thread_type'] === 'M' && $message_thread['user_id']) {
                        $person_email = $this->findUserEmailFromId($message_thread['user_id']);
                    }

                    $message_entity = new Entity\TicketMessage();
                    $message_entity
                        ->setPersonEmail($person_email)
                        ->setDateCreated(new DateTime($message_thread['created']))
                        ->setMessageText($message_thread['body']);

                    $attachments = $this->findTicketAttachment($ticket['ticket_id']);
                    if ($attachments) {
                        foreach ($attachments as $attachment) {
                            $file_data = $this->getFileData($attachment['file_id']);
                            $attachment_entity = new Entity\TicketMessageAttachment();
                            $attachment_entity
                                ->setOid($index)
                                ->setBlobData(base64_encode($file_data))
                                ->setFileName($attachment['name'])
                                ->setContentType($attachment['type']);

                            $message_entity->addAttachment($attachment_entity);
                        }
                    }

                    $ticket_entity->addMessage($message_entity);
                }

//                $file_name = $this->getExportTicketsOutputPath() . 'ticket' . $index++ . '.json';
//                $this->logInfo(sprintf('%s exported successfully!', $file_name));

                $collection[] = $ticket_entity;

                $offset++;
                $this->advanceProgressBar();
            }
        }

        return $collection;
    }
}

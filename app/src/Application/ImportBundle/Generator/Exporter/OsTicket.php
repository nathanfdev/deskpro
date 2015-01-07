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

use Application\ImportBundle\OsTicket\OsTicketReaderInterface;

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
    public function getTotalRecordsCount()
    {
        return $this->getRecordsCountByType(self::RECORD_TYPE_PEOPLE)
             + $this->getRecordsCountByType(self::RECORD_TYPE_TICKETS);
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
    public function generateJson()
    {
        try {
            $this->exportPeople();
            $this->exportTickets();

        } catch (\Exception $ex) {
            $this->logWarning($ex->getMessage());
        }
    }

    /**
     * @return void
     */
    protected function exportPeople()
    {
        $index  = 1;
        $offset = 0;

        $person_batch = $this->findAllStaff($offset);

        while ($person_batch) {
            foreach ($person_batch as $person) {
                $transformed = array(
                    'oid'          => $index,
                    'is_agent'     => true,
                    'first_name'   => $person['firstname'],
                    'last_name'    => $person['lastname'],
                    'timezone'     => $this->findTimezoneFromId($person['timezone_id']),
                    'date_created' => $person['created'],
                    'emails'       => array($person['email']),
                );

                $file_name = 'person' . $index . '.json';
                if ($this->config->isLive()) {
                    file_put_contents($this->getExportPeopleOutputPath() . $file_name, json_encode($transformed));
                }

                $this->logInfo(sprintf('%s exported successfully!', $file_name));

                $index++;
                $offset++;
            }

            unset($person);
            $person_batch = $this->findAllStaff($offset);
        }

        foreach ($this->findAllUser() as $person) {
            $transformed = array(
                'oid'          => $index,
                'is_user'      => true,
                'name'         => $person['name'],
                'date_created' => $person['created'],
                'emails'       => array($person['address']),
            );

            $file_name = 'person' . $index . '.json';
            if ($this->config->isLive()) {
                file_put_contents($this->getExportPeopleOutputPath() . $file_name, json_encode($transformed));
            }

            $this->advanceProgressBar();
            $this->logInfo(sprintf('%s exported successfully!', $file_name));

            $index++;
        }
    }

    /**
     * @return void
     */
    protected function exportTickets()
    {
        $index = 1;
        $offset = 0;

        $ticket_batch = $this->findAllTickets($offset);

        while ($ticket_batch) {
            foreach ($ticket_batch as $ticket) {
                $transformed = array(
                    'ref'          => !empty($ticket['number']) ? $ticket['number'] : null,
                    'department'   => $this->findDepartmentFromId($ticket['dept_id']),
                    'person'       => $this->findUserEmailFromId($ticket['user_id']),
                    'agent'        => $this->findUserEmailFromId($ticket['staff_id']) ?: null,
                    'agent_team'   => $this->findUserEmailFromId($ticket['team_id']) ?: null,
                    'status'       => $ticket['closed'] ? 'resolved' : $ticket['isanswered'] ? 'awaiting_user' : 'awaiting_agent',
                    'date_created' => $ticket['created'],
                    'subject'      => $ticket['subject'],
                    'priority'     => $ticket['priority'],
                );

                foreach ($this->findMessageThreadFromId($ticket['ticket_id']) as $message_thread) {
                    $person_email = null;
                    if ($message_thread['thread_type'] === 'R' && $message_thread['staff_id']) {
                        $person_email = $this->findStaffEmailFromId($message_thread['staff_id']);

                    } elseif ($message_thread['thread_type'] === 'M' && $message_thread['user_id']) {
                        $person_email = $this->findUserEmailFromId($message_thread['user_id']);
                    }

                    $message_array = array(
                        'person'       => $person_email,
                        'date_created' => $message_thread['created'],
                        'message_text' => $message_thread['body']
                    );

                    if ($this->findTicketAttachment($ticket['ticket_id'])) {
                        $attachments = $this->findTicketAttachment($ticket['ticket_id']);

                        foreach ($attachments as $attachment) {
                            $file_data = $this->getFileData($attachment['file_id']);
                            $message_array['attachments'][] = array(
                                'oid'          => $index,
                                'blob_data'    => base64_encode($file_data),
                                'file_name'    => $attachment['name'],
                                'content_type' => $attachment['type']
                            );
                        }
                    }

                    $transformed['messages'][] = $message_array;
                }

                $file_name = 'ticket' . $index++ . '.json';
                if ($this->config->isLive()) {
                    file_put_contents($this->getExportTicketsOutputPath() . $file_name, json_encode($transformed));
                }

                unset($transformed);
                $offset++;

                $this->logInfo(sprintf('%s exported successfully!', $file_name));
                $this->advanceProgressBar();
            }

            $ticket_batch = $this->findAllTickets($offset);
        }
    }
}

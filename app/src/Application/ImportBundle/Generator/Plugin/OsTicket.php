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

namespace Application\ImportBundle\Generator\Plugin;

/**
 * Description of OsTicket
 *
 * @author Abhinav Kumar <abhinav.kumar@deskpro.com>
 * @package Application\ImportBundle\Generator\Plugin
 */
class OsTicket extends AbstractPlugin
{
    /**
     * @var \PDO
     */
    protected $db;

    /**
     * @var int|null
     */
    protected $ticket_offset;

    /**
     * @var int
     */
    protected $batch_size;

    /**
     * Constructor
     *
     * @throws \Exception
     */
    public function __construct()
    {
        $os_config = dp_get_config('osticket_import');

        $db_host = $os_config['db_host'];
        $db_name = $os_config['db_name'];
        $db_username = $os_config['db_username'];
        $db_password = $os_config['db_password'];

        $this->batch_size = 10;
//        $this->db = new \PDO("mysql:dbname={$db_name};host={$db_host}", $db_username, $db_password);
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
    public function generateJson()
    {
        $steps = $this->getPeopleCount() + $this->getTicketCount();

        $this->config->getProgressBarHelper()->start($this->config->output, $steps);

        try {
            $this->exportPeople();
            $this->exportTickets();
        } catch (\Exception $ex) {
            //$this->logger->warning($ex->getMessage());
        }
    }

    protected function getTicketCount()
    {
        $query = 'SELECT count(ticket_id) FROM ost_ticket';

        $stmt   = $this->db->prepare($query);

        $stmt->execute();

        return $stmt->fetchColumn();
    }

    protected function getPeopleCount()
    {
        $query = 'SELECT count(staff_id) FROM ost_staff';
        $stmt   = $this->db->prepare($query);
        $stmt->execute();
        $staff_count = $stmt->fetchColumn();

        $query = 'SELECT count(id) FROM ost_user';
        $stmt   = $this->db->prepare($query);
        $stmt->execute();
        $user_count = $stmt->fetchColumn();

        return $staff_count + $user_count;
    }

    protected function findAllTickets($offset)
    {
        $query = 'SELECT * FROM ost_ticket t LEFT JOIN ost_ticket__cdata c ON t.ticket_id = c.ticket_id'
        . ' LIMIT :limit'
        . ' OFFSET :offset';

        $stmt   = $this->db->prepare($query);

        $stmt->bindValue(':limit', (int) $this->batch_size, \PDO::PARAM_INT);
        $stmt->bindValue(':offset', (int) $offset, \PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    protected function findTicketAttachment($ticket_id)
    {
        $ticket_id = (int) $ticket_id;

        $query = 'SELECT f.name, f.type, a.file_id  FROM ost_file f JOIN ost_ticket_attachment a '
        . ' ON f.id = a.file_id'
        . ' WHERE ticket_id = ?';

        $stmt   = $this->db->prepare($query);
        $stmt->execute(array($ticket_id));

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    protected function findAllStaff($offset = 0)
    {
        $query = 'SELECT * FROM ost_staff LIMIT :limit OFFSET :offset';

        $stmt   = $this->db->prepare($query);

        $stmt->bindValue(':limit', (int) $this->batch_size, \PDO::PARAM_INT);
        $stmt->bindValue(':offset', (int) $offset, \PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    protected function findAllUser($offset = 0)
    {
        $query = 'SELECT * FROM ost_user u LEFT JOIN ost_user_email e ON u.id = e.user_id'
        . ' LIMIT :limit'
        . ' OFFSET :offset';

        $stmt   = $this->db->prepare($query);

        $stmt->bindValue(':limit', (int) $this->batch_size, \PDO::PARAM_INT);
        $stmt->bindValue(':offset', (int) $offset, \PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    protected function findDepartmentFromId($id)
    {
        $query = 'SELECT dept_name FROM ost_department WHERE dept_id = ?';

        $stmt   = $this->db->prepare($query);
        $stmt->execute(array($id));

        return $stmt->fetchColumn();
    }

    protected function findUserEmailFromId($id)
    {
        $query = 'SELECT address FROM ost_user_email e'
        . ' LEFT JOIN ost_user u '
        . ' ON e.user_id=u.id'
        . ' WHERE u.id = ?';

        $stmt   = $this->db->prepare($query);
        $stmt->execute(array($id));

        return $stmt->fetchColumn();
    }

    protected function findStaffEmailFromId($id)
    {
        $query = 'SELECT email FROM ost_staff WHERE id = ?';

        $stmt   = $this->db->prepare($query);
        $stmt->execute(array($id));

        return $stmt->fetchColumn();
    }

    protected function findTeamNameFromId($id)
    {
        $query = 'SELECT name FROM ost_team WHERE id = ?';

        $stmt   = $this->db->prepare($query);
        $stmt->execute(array($id));

        return $stmt->fetchColumn();
    }

    protected function findMessageThreadFromId($ticket_id)
    {
        $query = 'SELECT thread_type, staff_id, user_id, body, created FROM ost_ticket_thread WHERE ticket_id = ?';

        $stmt   = $this->db->prepare($query);
        $stmt->execute(array($ticket_id));

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    protected function findTimezoneFromId($id)
    {
        $query = 'SELECT timezone FROM ost_timezone WHERE id = ?';

        $stmt   = $this->db->prepare($query);
        $stmt->execute(array($id));

        return $stmt->fetchColumn();
    }

    protected function getFileData($file_id)
    {
        $data = '';

        $query = 'SELECT filedata FROM ost_file_chunk WHERE file_id = ?';

        $stmt   = $this->db->prepare($query);
        $stmt->execute(array($file_id));

        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        foreach ($rows as $chunk) {
            $data .= $chunk['filedata'];
        }

        return $data;
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
                $transformedArray = array(
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
                    file_put_contents($this->getExportPeopleOutputPath() . $file_name, json_encode($transformedArray));
                }

                //$this->logger->info(sprintf('%s exported successfully!', $file_name));

                $index++;
                $offset++;
            }

            unset($person);
            $person_batch = $this->findAllStaff($offset);
        }

        foreach ($this->findAllUser() as $person) {
            $transformedArray = array(
                'oid'          => $index,
                'is_user'      => true,
                'name'         => $person['name'],
                'date_created' => $person['created'],
                'emails'       => array($person['address']),
            );

            $file_name = 'person' . $index . '.json';
            if ($this->config->isLive()) {
                file_put_contents($this->getExportPeopleOutputPath() . $file_name, json_encode($transformedArray));
            }

            $this->config->getProgressBarHelper()->advance();
            //$this->logger->info(sprintf('%s exported successfully!', $file_name));

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
                $transformedArray = array(
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

                    $transformedArray['messages'][] = $message_array;
                }

                $file_name = 'ticket' . $index++ . '.json';

                if ($this->config->isLive()) {
                    file_put_contents($this->getExportTicketsOutputPath() . $file_name, json_encode($transformedArray));
                }

                unset($transformedArray);

                $offset++;

                //$this->logger->info(sprintf('%s exported successfully!', $file_name));

                $this->config->getProgressBarHelper()->advance();
            }

            $ticket_batch = $this->findAllTickets($offset);
        }
    }
}

<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2016, DeskPRO Ltd.
 *
 * The license agreement under which this software is released
 * can be found at https://www.deskpro.com/eula/
 *
 * By using this software, you acknowledge having read the license
 * and agree to be bound thereby.
 *
 * Please note that DeskPRO is not free software. We release the full
 * source code for our software because we trust our users to pay us for
 * the huge investment in time and energy that has gone into both creating
 * this software and supporting our customers. By providing the source code
 * we preserve our customers' ability to modify, audit and learn from our
 * work. We have been developing DeskPRO since 2001, please help us make it
 * another decade.
 *
 * Like the work you see? Think you could make it better? We are always
 * looking for great developers to join us: http://www.deskpro.com/jobs/
 *
 * ~ Thanks, Everyone at Team DeskPRO
 */

namespace Application\ImportBundle\Reader\OsTicket;

use Application\ImportBundle\Reader\AbstractReader;
use PDO;

/**
 * Os ticket reader.
 *
 * Table os ticket not found by default, use this query to create:
 *
 * CREATE TABLE ost_ticket__cdata (
 *     PRIMARY KEY (ticket_id)
 * ) AS SELECT entry.object_id AS ticket_id,
 *     MAX(IF(field.name = 'subject',
 *         ans.value,
 *         NULL)) AS `subject`,
 *     MAX(IF(field.name = 'priority',
 *         ans.value,
 *         NULL)) AS `priority_desc`,
 *     MAX(IF(field.name = 'priority',
 *         ans.value_id,
 *         NULL)) AS `priority_id` FROM
 *     ost_form_entry entry
 *         LEFT JOIN
 *     ost_form_entry_values ans ON ans.entry_id = entry.id
 *         LEFT JOIN
 *     ost_form_field field ON field.id = ans.field_id
 * WHERE
 *     entry.object_type = 'T'
 * GROUP BY entry.object_id;
 *
 * Class OsTicketReader
 */
class OsTicketReader extends AbstractReader implements OsTicketReaderInterface
{
    /**
     * @var ConnectionWrapperInterface
     */
    private $connection_wrapper;

    /**
     * @var array
     */
    private $timezones = [];

    /**
     * @var bool
     */
    private $timezones_loaded = false;

    /**
     * @var array
     */
    private $ticket_priorities = [];

    /**
     * @var bool
     */
    private $ticket_priorities_loaded = false;

    /**
     * Constructor.
     *
     * @param OsTicketConfig $config
     */
    public function __construct(OsTicketConfig $config)
    {
        parent::__construct($config);

        $dsn = sprintf('mysql:dbname=%s;host=%s', $config->getDatabase(), $config->getHost());
        if ($config->getPort()) {
            $dsn .= sprintf(';port=%s', $config->getPort());
        }

        $this->connection_wrapper = new LazyConnectionWrapper($dsn, $config->getUser(), $config->getPassword());
    }

    /**
     * {@inheritdoc}
     */
    public function checkConfig()
    {
        $stmt = $this->getConnection()->prepare('SHOW TABLES');
        if ($stmt->execute() === false) {
            throw new OsTicketReaderException('Unable to get a list of tables', $stmt->errorCode(), $stmt->errorInfo());
        }

        $tables = [];
        $result = $stmt->fetchAll(PDO::FETCH_NUM);

        foreach ($result as $table_info) {
            $tables[] = $table_info[0];
        }

        $check_tables = [
            'ost_staff',
            'ost_user',
            'ost_ticket',
            'ost_ticket_thread',
            'ost_ticket_attachment',
            'ost_department',
            'ost_organization',
            'ost_groups',
            'ost_user_email',
            'ost_team',
            'ost_file_chunk',
            'ost_timezone',
            'ost_ticket_priority',
        ];

        $exist_tables = array_intersect($tables, $check_tables);

        sort($exist_tables);
        sort($check_tables);

        if ($exist_tables != $check_tables) {
            throw new \RuntimeException(sprintf(
                'Not all required tables found, please check for: %s.',
                implode(', ', array_map(
                    function ($table) {
                        return '`'.$table.'`';
                    },
                    array_diff($check_tables, $exist_tables)
                ))
            ));
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function getStaffCount($min_id = 0)
    {
        $query = 'SELECT count(staff_id) FROM ost_staff WHERE staff_id > :min_id ORDER BY staff_id ASC';
        $stmt  = $this->getConnection()->prepare($query);
        $stmt->bindValue(':min_id', (int) $min_id, PDO::PARAM_INT);

        if ($stmt->execute() === false) {
            throw new OsTicketReaderException('Unable to get staff count', $stmt->errorCode(), $stmt->errorInfo());
        }

        return (int) $stmt->fetchColumn();
    }

    /**
     * {@inheritdoc}
     */
    public function getUsersCount($min_id = 0)
    {
        $query = 'SELECT count(id) FROM ost_user WHERE id > :min_id ORDER BY id ASC';
        $stmt  = $this->getConnection()->prepare($query);
        $stmt->bindValue(':min_id', (int) $min_id, PDO::PARAM_INT);

        if ($stmt->execute() === false) {
            throw new OsTicketReaderException('Unable to get users count', $stmt->errorCode(), $stmt->errorInfo());
        }

        return (int) $stmt->fetchColumn();
    }

    /**
     * {@inheritdoc}
     */
    public function getTicketsCount($min_id = 0)
    {
        $query = 'SELECT count(ticket_id) FROM ost_ticket WHERE ticket_id > :min_id ORDER BY ticket_id ASC';
        $stmt  = $this->getConnection()->prepare($query);
        $stmt->bindValue(':min_id', (int) $min_id, PDO::PARAM_INT);

        if ($stmt->execute() === false) {
            throw new OsTicketReaderException('Unable to get tickets count', $stmt->errorCode(), $stmt->errorInfo());
        }

        return $stmt->fetchColumn();
    }

    /**
     * {@inheritdoc}
     */
    public function findStaff($limit, $min_id = 0)
    {
        $query = 'SELECT * FROM ost_staff WHERE staff_id > :min_id ORDER BY staff_id ASC LIMIT :limit';
        $stmt  = $this->getConnection()->prepare($query);
        $stmt->bindValue(':limit',  (int) $limit,  PDO::PARAM_INT);
        $stmt->bindValue(':min_id', (int) $min_id, PDO::PARAM_INT);

        if ($stmt->execute() === false) {
            throw new OsTicketReaderException('Unable to find staff', $stmt->errorCode(), $stmt->errorInfo());
        }

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * {@inheritdoc}
     */
    public function findStaffByIds(array $ids)
    {
        if (empty($ids)) {
            throw new OsTicketReaderException('Empty ids list');
        }

        $marks = implode(',', array_fill(0, count($ids), '?'));
        $query = 'SELECT * FROM ost_staff WHERE staff_id IN ('.$marks.') ORDER BY staff_id ASC';

        $stmt = $this->getConnection()->prepare($query);

        if ($stmt->execute($ids) === false) {
            throw new OsTicketReaderException('Unable to find users', $stmt->errorCode(), $stmt->errorInfo());
        }

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * {@inheritdoc}
     */
    public function findUsers($limit, $min_id = 0)
    {
        $query = 'SELECT *, u.id user_id FROM ost_user u LEFT JOIN ost_user_email e ON u.id = e.user_id WHERE u.id > :min_id ORDER BY u.id ASC LIMIT :limit';
        $stmt  = $this->getConnection()->prepare($query);
        $stmt->bindValue(':limit',  (int) $limit,  PDO::PARAM_INT);
        $stmt->bindValue(':min_id', (int) $min_id, PDO::PARAM_INT);

        if ($stmt->execute() === false) {
            throw new OsTicketReaderException('Unable to find users', $stmt->errorCode(), $stmt->errorInfo());
        }

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * {@inheritdoc}
     */
    public function findUsersByIds(array $ids)
    {
        if (empty($ids)) {
            throw new OsTicketReaderException('Empty ids list');
        }

        $marks = implode(',', array_fill(0, count($ids), '?'));
        $query = 'SELECT *, u.id user_id FROM ost_user u LEFT JOIN ost_user_email e ON u.id = e.user_id WHERE u.id IN ('.$marks.') ORDER BY u.id ASC';

        $stmt = $this->getConnection()->prepare($query);

        if ($stmt->execute($ids) === false) {
            throw new OsTicketReaderException('Unable to find users', $stmt->errorCode(), $stmt->errorInfo());
        }

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * {@inheritdoc}
     */
    public function findTickets($limit, $min_id = 0)
    {
        $query = 'SELECT * FROM ost_ticket t LEFT JOIN ost_ticket__cdata c ON t.ticket_id = c.ticket_id WHERE t.ticket_id > :min_id ORDER BY t.ticket_id ASC LIMIT :limit';
        $stmt  = $this->getConnection()->prepare($query);
        $stmt->bindValue(':limit',  (int) $limit,  PDO::PARAM_INT);
        $stmt->bindValue(':min_id', (int) $min_id, PDO::PARAM_INT);

        if ($stmt->execute() === false) {
            throw new OsTicketReaderException('Unable to find tickets', $stmt->errorCode(), $stmt->errorInfo());
        }

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * {@inheritdoc}
     */
    public function findMessages($ticket_id)
    {
        $query = 'SELECT id, thread_type, staff_id, user_id, body, created FROM ost_ticket_thread WHERE ticket_id = :ticket_id';
        $stmt  = $this->getConnection()->prepare($query);
        $stmt->bindValue(':ticket_id', (int) $ticket_id, PDO::PARAM_INT);

        if ($stmt->execute() === false) {
            throw new OsTicketReaderException('Unable to find ticket messages', $stmt->errorCode(), $stmt->errorInfo());
        }

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * {@inheritdoc}
     */
    public function findMessageAttachments($message_id)
    {
        $query = 'SELECT f.name, f.type, a.file_id FROM ost_file f JOIN ost_ticket_attachment a ON f.id = a.file_id WHERE a.ref_id = :message_id';
        $stmt  = $this->getConnection()->prepare($query);
        $stmt->bindValue(':message_id', (int) $message_id, PDO::PARAM_INT);

        if ($stmt->execute() === false) {
            throw new OsTicketReaderException('Unable to find message attachments', $stmt->errorCode(), $stmt->errorInfo());
        }

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * {@inheritdoc}
     */
    public function findDepartmentById($id)
    {
        $query = 'SELECT dept_name FROM ost_department WHERE dept_id = :id';
        $stmt  = $this->getConnection()->prepare($query);
        $stmt->bindValue(':id', (int) $id, PDO::PARAM_INT);

        if ($stmt->execute() === false) {
            throw new OsTicketReaderException('Unable to find department', $stmt->errorCode(), $stmt->errorInfo());
        }

        return $stmt->fetchColumn();
    }

    /**
     * {@inheritdoc}
     */
    public function findOrganizationNameById($id)
    {
        $query = 'SELECT name FROM ost_organization WHERE id = :id';
        $stmt  = $this->getConnection()->prepare($query);
        $stmt->bindValue(':id', (int) $id, PDO::PARAM_INT);

        if ($stmt->execute() === false) {
            throw new OsTicketReaderException('Unable to find organization', $stmt->errorCode(), $stmt->errorInfo());
        }

        return $stmt->fetchColumn();
    }

    /**
     * {@inheritdoc}
     */
    public function findUserGroupNameById($id)
    {
        $query = 'SELECT group_name FROM ost_groups WHERE group_id = :id';
        $stmt  = $this->getConnection()->prepare($query);
        $stmt->bindValue(':id', (int) $id, PDO::PARAM_INT);

        if ($stmt->execute() === false) {
            throw new OsTicketReaderException('Unable to find user group', $stmt->errorCode(), $stmt->errorInfo());
        }

        return $stmt->fetchColumn();
    }

    /**
     * {@inheritdoc}
     */
    public function findUserEmailById($id)
    {
        $query = 'SELECT address FROM ost_user_email e LEFT JOIN ost_user u ON e.user_id = u.id WHERE u.id = :id';
        $stmt  = $this->getConnection()->prepare($query);
        $stmt->bindValue(':id', (int) $id, PDO::PARAM_INT);

        if ($stmt->execute() === false) {
            throw new OsTicketReaderException('Unable to find user email', $stmt->errorCode(), $stmt->errorInfo());
        }

        return $stmt->fetchColumn();
    }

    /**
     * {@inheritdoc}
     */
    public function findStaffEmailById($id)
    {
        $query = 'SELECT email FROM ost_staff WHERE staff_id = :id';
        $stmt  = $this->getConnection()->prepare($query);
        $stmt->bindValue(':id', (int) $id, PDO::PARAM_INT);

        if ($stmt->execute() === false) {
            throw new OsTicketReaderException('Unable to find staff email', $stmt->errorCode(), $stmt->errorInfo());
        }

        return $stmt->fetchColumn();
    }

    /**
     * {@inheritdoc}
     */
    public function findTeamNameById($id)
    {
        $query = 'SELECT name FROM ost_team WHERE team_id = :id';
        $stmt  = $this->getConnection()->prepare($query);
        $stmt->bindValue(':id', (int) $id, PDO::PARAM_INT);

        if ($stmt->execute() === false) {
            throw new OsTicketReaderException('Unable to find team name', $stmt->errorCode(), $stmt->errorInfo());
        }

        return $stmt->fetchColumn();
    }

    /**
     * {@inheritdoc}
     */
    public function findTimezoneById($id)
    {
        if ($this->timezones_loaded === false) {
            $this->loadTimezones();
            $this->timezones_loaded = true;
        }

        $id = (int) $id;
        if (isset($this->timezones[$id])) {
            $timezone = $this->timezones[$id];

            return TimeZoneMapper::getTimeZoneName($timezone['offset'], $timezone['timezone']);
        }

        return;
    }

    /**
     * {@inheritdoc}
     */
    public function findAttachmentData($file_id)
    {
        $query = 'SELECT filedata FROM ost_file_chunk WHERE file_id = :file_id';
        $stmt  = $this->getConnection()->prepare($query);
        $stmt->bindValue(':file_id', (int) $file_id, PDO::PARAM_INT);

        if ($stmt->execute() === false) {
            throw new OsTicketReaderException('Unable to find attachment data', $stmt->errorCode(), $stmt->errorInfo());
        }

        $data = '';
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($rows as $chunk) {
            $data .= $chunk['filedata'];
        }

        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function findTicketPriority($id)
    {
        if ($this->ticket_priorities_loaded === false) {
            $this->loadTicketPriorities();
            $this->ticket_priorities_loaded = true;
        }

        $id = (int) $id;
        if (isset($this->ticket_priorities[$id])) {
            return $this->ticket_priorities[$id];
        }

        return;
    }

    /**
     * Loads all timezones.
     *
     * @throws OsTicketReaderException
     */
    private function loadTimezones()
    {
        $query = 'SELECT * FROM ost_timezone';
        $stmt  = $this->getConnection()->prepare($query);

        if ($stmt->execute() === false) {
            throw new OsTicketReaderException('Unable to find timezones', $stmt->errorCode(), $stmt->errorInfo());
        }

        $this->timezones = [];
        $rows            = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($rows as $row) {
            $this->timezones[(int) $row['id']] = $row;
        }
    }

    /**
     * Loads all ticket priorities.
     *
     * @throws OsTicketReaderException
     */
    private function loadTicketPriorities()
    {
        $query = 'SELECT * FROM ost_ticket_priority';
        $stmt  = $this->getConnection()->prepare($query);

        if ($stmt->execute() === false) {
            throw new OsTicketReaderException('Unable to find ticket priorities', $stmt->errorCode(), $stmt->errorInfo());
        }

        $this->ticket_priorities = [];
        $rows                    = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($rows as $row) {
            $this->ticket_priorities[(int) $row['priority_id']] = $row;
        }
    }

    /**
     * Returns pdo connection.
     *
     * @return PDO
     */
    private function getConnection()
    {
        return $this->connection_wrapper->getConnection();
    }
}

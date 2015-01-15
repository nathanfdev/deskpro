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

namespace Application\ImportBundle\OsTicket;

use Pdo;

/**
 * Os ticket reader
 *
 * Class OsTicketReader
 * @package Application\ImportBundle\OsTicket
 */
class OsTicketReader implements OsTicketReaderInterface
{
    /**
     * @var ConnectionWrapperInterface
     */
    private $connection_wrapper;

    /**
     * Constructor
     *
     * @param ConnectionWrapperInterface $connection_wrapper
     */
    public function __construct(ConnectionWrapperInterface $connection_wrapper)
    {
        $this->connection_wrapper = $connection_wrapper;
    }

    /**
     * {@inheritdoc}
     */
    public function getTicketsCount()
    {
        $query = 'SELECT count(ticket_id) FROM ost_ticket';
        $stmt  = $this->getConnection()->prepare($query);
        if ($stmt->execute() === false) {
            throw new OsTicketReaderException('Unable to get tickets count', $stmt->errorCode(), $stmt->errorInfo());
        }

        return $stmt->fetchColumn();
    }

    /**
     * {@inheritdoc}
     */
    public function getPeopleCount()
    {
        $query = 'SELECT count(staff_id) FROM ost_staff';
        $stmt  = $this->getConnection()->prepare($query);
        if ($stmt->execute() === false) {
            throw new OsTicketReaderException('Unable to get staff count', $stmt->errorCode(), $stmt->errorInfo());
        }

        $staff_count = (int)$stmt->fetchColumn();

        $query = 'SELECT count(id) FROM ost_user';
        $stmt  = $this->getConnection()->prepare($query);
        if ($stmt->execute() === false) {
            throw new OsTicketReaderException('Unable to get users count', $stmt->errorCode(), $stmt->errorInfo());
        }

        $user_count = (int)$stmt->fetchColumn();
        return $staff_count + $user_count;
    }

    /**
     * {@inheritdoc}
     */
    public function findStaff($limit, $offset)
    {
        $query = 'SELECT * FROM ost_staff LIMIT :limit OFFSET :offset';
        $stmt  = $this->getConnection()->prepare($query);

        $stmt->bindValue(':limit',  (int)$limit,  PDO::PARAM_INT);
        $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
        if ($stmt->execute() === false) {
            throw new OsTicketReaderException('Unable to find staff', $stmt->errorCode(), $stmt->errorInfo());
        }

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * {@inheritdoc}
     */
    public function findUsers($limit, $offset)
    {
        $query = 'SELECT * FROM ost_user u LEFT JOIN ost_user_email e ON u.id = e.user_id LIMIT :limit OFFSET :offset';
        $stmt  = $this->getConnection()->prepare($query);

        $stmt->bindValue(':limit',  (int)$limit,  PDO::PARAM_INT);
        $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
        if ($stmt->execute() === false) {
            throw new OsTicketReaderException('Unable to find users', $stmt->errorCode(), $stmt->errorInfo());
        }

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * {@inheritdoc}
     */
    public function findTickets($limit, $offset)
    {
        $query = 'SELECT * FROM ost_ticket t LEFT JOIN ost_ticket__cdata c ON t.ticket_id = c.ticket_id'
            . ' LIMIT :limit'
            . ' OFFSET :offset';

        $stmt = $this->getConnection()->prepare($query);
        $stmt->bindValue(':limit',  (int)$limit,  PDO::PARAM_INT);
        $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
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
        $query = 'SELECT id, thread_type, staff_id, user_id, body, created FROM ost_ticket_thread WHERE ticket_id = ?';
        $stmt  = $this->getConnection()->prepare($query);
        if ($stmt->execute($ticket_id) === false) {
            throw new OsTicketReaderException('Unable to find ticket messages', $stmt->errorCode(), $stmt->errorInfo());
        }

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * {@inheritdoc}
     */
    public function findMessageAttachments($message_id)
    {
        $query = 'SELECT f.name, f.type, a.file_id  FROM ost_file f JOIN ost_ticket_attachment a '
            . ' ON f.id = a.file_id'
            . ' WHERE a.ref_id = ?';

        $stmt = $this->getConnection()->prepare($query);
        if ($stmt->execute(array((int)$message_id)) === false) {
            throw new OsTicketReaderException('Unable to find message attachments', $stmt->errorCode(), $stmt->errorInfo());
        }

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * {@inheritdoc}
     */
    public function findDepartmentById($id)
    {
        $query = 'SELECT dept_name FROM ost_department WHERE dept_id = ?';
        $stmt  = $this->getConnection()->prepare($query);
        if ($stmt->execute(array((int)$id)) === false) {
            throw new OsTicketReaderException('Unable to find department', $stmt->errorCode(), $stmt->errorInfo());
        }

        return $stmt->fetchColumn();
    }

    /**
     * {@inheritdoc}
     */
    public function findUserEmailById($id)
    {
        $query = 'SELECT address FROM ost_user_email e'
            . ' LEFT JOIN ost_user u '
            . ' ON e.user_id=u.id'
            . ' WHERE u.id = ?';

        $stmt = $this->getConnection()->prepare($query);
        if ($stmt->execute(array((int)$id)) === false) {
            throw new OsTicketReaderException('Unable to find user email', $stmt->errorCode(), $stmt->errorInfo());
        }

        return $stmt->fetchColumn();
    }

    /**
     * {@inheritdoc}
     */
    public function findStaffEmailById($id)
    {
        $query = 'SELECT email FROM ost_staff WHERE id = ?';
        $stmt  = $this->getConnection()->prepare($query);
        if ($stmt->execute(array((int)$id)) === false) {
            throw new OsTicketReaderException('Unable to find staff email', $stmt->errorCode(), $stmt->errorInfo());
        }

        return $stmt->fetchColumn();
    }

    /**
     * {@inheritdoc}
     */
    public function findTeamNameById($id)
    {
        $query = 'SELECT name FROM ost_team WHERE id = ?';
        $stmt  = $this->getConnection()->prepare($query);
        if ($stmt->execute(array((int)$id)) === false) {
            throw new OsTicketReaderException('Unable to find team name', $stmt->errorCode(), $stmt->errorInfo());
        }

        return $stmt->fetchColumn();
    }

    /**
     * {@inheritdoc}
     */
    public function findTimezoneById($id)
    {
        $query = 'SELECT timezone FROM ost_timezone WHERE id = ?';
        $stmt  = $this->getConnection()->prepare($query);
        if ($stmt->execute(array((int)$id)) === false) {
            throw new OsTicketReaderException('Unable to find timezone', $stmt->errorCode(), $stmt->errorInfo());
        }

        return $stmt->fetchColumn();
    }

    /**
     * {@inheritdoc}
     */
    public function getAttachmentData($file_id)
    {
        $data  = '';
        $query = 'SELECT filedata FROM ost_file_chunk WHERE file_id = ?';
        $stmt  = $this->getConnection()->prepare($query);
        $stmt->execute(array($file_id));
        if ($stmt->execute(array((int)$file_id)) === false) {
            throw new OsTicketReaderException('Unable to find attachment data', $stmt->errorCode(), $stmt->errorInfo());
        }

        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($rows as $chunk) {
            $data .= $chunk['filedata'];
        }

        return $data;
    }

    /**
     * Returns pdo connection
     *
     * @return PDO
     */
    private function getConnection()
    {
        return $this->connection_wrapper->getConnection();
    }
}

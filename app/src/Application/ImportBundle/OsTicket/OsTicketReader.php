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
 * Class OsTicketReader
 * @package Application\ImportBundle\OsTicket
 */
class OsTicketReader implements OsTicketReaderInterface
{
    /**
     * @var PDO
     */
    private $db;

    /**
     * Constructor
     *
     * @param PDO $db
     */
    public function __construct(/*PDO*/ $db)
    {
        $this->db = $db;
    }

    /**
     * {@inheritdoc}
     */
    public function getTicketCount()
    {
        $query = 'SELECT count(ticket_id) FROM ost_ticket';
        $stmt   = $this->db->prepare($query);

        $stmt->execute();

        return $stmt->fetchColumn();
    }

    /**
     * {@inheritdoc}
     */
    public function getPeopleCount()
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

    public function findAllTickets($offset)
    {
        $query = 'SELECT * FROM ost_ticket t LEFT JOIN ost_ticket__cdata c ON t.ticket_id = c.ticket_id'
            . ' LIMIT :limit'
            . ' OFFSET :offset';

        $stmt   = $this->db->prepare($query);

        $stmt->bindValue(':limit', (int) $this->config->getBatchSize(), \PDO::PARAM_INT);
        $stmt->bindValue(':offset', (int) $offset, \PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function findTicketAttachment($ticket_id)
    {
        $ticket_id = (int) $ticket_id;

        $query = 'SELECT f.name, f.type, a.file_id  FROM ost_file f JOIN ost_ticket_attachment a '
            . ' ON f.id = a.file_id'
            . ' WHERE ticket_id = ?';

        $stmt   = $this->db->prepare($query);
        $stmt->execute(array($ticket_id));

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function findAllStaff($offset = 0)
    {
        $query = 'SELECT * FROM ost_staff LIMIT :limit OFFSET :offset';
        $stmt  = $this->db->prepare($query);

        $stmt->bindValue(':limit', (int) $this->config->getBatchSize(), \PDO::PARAM_INT);
        $stmt->bindValue(':offset', (int) $offset, \PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function findAllUser($offset = 0)
    {
        $query = 'SELECT * FROM ost_user u LEFT JOIN ost_user_email e ON u.id = e.user_id'
            . ' LIMIT :limit'
            . ' OFFSET :offset';

        $stmt   = $this->db->prepare($query);

        $stmt->bindValue(':limit', (int) $this->config->getBatchSize(), \PDO::PARAM_INT);
        $stmt->bindValue(':offset', (int) $offset, \PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function findDepartmentFromId($id)
    {
        $query = 'SELECT dept_name FROM ost_department WHERE dept_id = ?';

        $stmt   = $this->db->prepare($query);
        $stmt->execute(array($id));

        return $stmt->fetchColumn();
    }

    public function findUserEmailFromId($id)
    {
        $query = 'SELECT address FROM ost_user_email e'
            . ' LEFT JOIN ost_user u '
            . ' ON e.user_id=u.id'
            . ' WHERE u.id = ?';

        $stmt   = $this->db->prepare($query);
        $stmt->execute(array($id));

        return $stmt->fetchColumn();
    }

    public function findStaffEmailFromId($id)
    {
        $query = 'SELECT email FROM ost_staff WHERE id = ?';

        $stmt   = $this->db->prepare($query);
        $stmt->execute(array($id));

        return $stmt->fetchColumn();
    }

    public function findTeamNameFromId($id)
    {
        $query = 'SELECT name FROM ost_team WHERE id = ?';

        $stmt   = $this->db->prepare($query);
        $stmt->execute(array($id));

        return $stmt->fetchColumn();
    }

    public function findMessageThreadFromId($ticket_id)
    {
        $query = 'SELECT thread_type, staff_id, user_id, body, created FROM ost_ticket_thread WHERE ticket_id = ?';

        $stmt   = $this->db->prepare($query);
        $stmt->execute(array($ticket_id));

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function findTimezoneFromId($id)
    {
        $query = 'SELECT timezone FROM ost_timezone WHERE id = ?';

        $stmt   = $this->db->prepare($query);
        $stmt->execute(array($id));

        return $stmt->fetchColumn();
    }

    public function getFileData($file_id)
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
}

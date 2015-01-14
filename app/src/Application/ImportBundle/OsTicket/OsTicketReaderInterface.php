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

/**
 * Os ticket reader interface
 *
 * Interface OsTicketReaderInterface
 * @package Application\ImportBundle\OsTicket
 */
interface OsTicketReaderInterface
{
    /**
     * @return int
     */
    public function getPeopleCount();

    /**
     * @return int
     */
    public function getTicketCount();

    /**
     * @param int $limit
     * @param int $offset
     *
     * @return mixed
     */
    public function findStaff($limit, $offset);

    /**
     * @param int $limit
     * @param int $offset
     *
     * @return mixed
     */
    public function findUsers($limit, $offset);

    /**
     * @param int $limit
     * @param int $offset
     *
     * @return array|false
     */
    public function findTickets($limit, $offset);

    /**
     * @param int $message_id
     * @return array
     */
    public function findMessageAttachments($message_id);

    /**
     * @param $id
     * @return mixed
     */
    public function findDepartmentById($id);

    /**
     * @param $id
     * @return mixed
     */
    public function findUserEmailById($id);

    /**
     * @param $id
     * @return mixed
     */
    public function findStaffEmailFromId($id);

    /**
     * @param $id
     * @return mixed
     */
    public function findTeamNameFromId($id);

    /**
     * @param $ticket_id
     * @return mixed
     */
    public function findMessages($ticket_id);

    /**
     * @param $id
     * @return mixed
     */
    public function findTimezoneFromId($id);

    /**
     * @param $file_id
     * @return mixed
     */
    public function getAttachmentData($file_id);
}

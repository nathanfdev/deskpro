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



namespace Application\ImportBundle\Reader\DeskPRO;


use Application\ImportBundle\Reader\BaseConfig;

class Config extends BaseConfig
{
    protected $host;

    protected $database;

    protected $user;

    protected $password;

    protected $start_ticket_id;

    public function __construct($host, $db, $user, $password, $start_ticket_id = 0)
    {
        $this->host = $host;
        $this->database = $db;
        $this->user = $user;
        $this->password = $password;
        $this->start_ticket_id = (int) $start_ticket_id;
    }

    /**
     * @return mixed
     */
    public function getHost()
    {
        return $this->host;
    }

    /**
     * @return mixed
     */
    public function getDatabase()
    {
        return $this->database;
    }

    /**
     * @return mixed
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @return mixed
     */
    public function getPassword()
    {
        return $this->password;
    }

    public function getStartTicketId()
    {
        return $this->start_ticket_id;
    }

    static public function fromArray(array $data)
    {
        return new self(
            $data['host'],
            $data['db'],
            $data['user'],
            $data['password'],
            @$data['start_ticket_id']
        );
    }
}
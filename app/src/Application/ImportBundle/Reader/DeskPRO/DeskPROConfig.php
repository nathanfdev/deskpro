<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2015, DeskPRO Ltd.
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

namespace Application\ImportBundle\Reader\DeskPRO;

use Application\ImportBundle\Reader\ReaderConfigInterface;

/**
 * Class DeskPROConfig.
 */
class DeskPROConfig implements ReaderConfigInterface
{
    /**
     * @var string
     */
    protected $host;

    /**
     * @var int
     */
    protected $port;

    /**
     * @var string
     */
    protected $database;

    /**
     * @var string
     */
    protected $user;

    /**
     * @var string
     */
    protected $password;

    /**
     * @var int
     */
    protected $start_ticket_id;

    /**
     * Constructor.
     *
     * @param string $host
     * @param int    $port
     * @param string $db
     * @param string $user
     * @param string $password
     * @param int    $start_ticket_id
     */
    public function __construct($host, $port, $db, $user, $password, $start_ticket_id = 0)
    {
        $this->host            = $host;
        $this->port            = $port;
        $this->database        = $db;
        $this->user            = $user;
        $this->password        = $password;
        $this->start_ticket_id = (int) $start_ticket_id;
    }

    /**
     * @return string
     */
    public function getHost()
    {
        return $this->host;
    }

    /**
     * @return int
     */
    public function getPort()
    {
        return $this->port;
    }

    /**
     * @return string
     */
    public function getDatabase()
    {
        return $this->database;
    }

    /**
     * @return string
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * @return int
     */
    public function getStartTicketId()
    {
        return $this->start_ticket_id;
    }

    /**
     * {@inheritdoc}
     */
    public static function fromArray(array $data)
    {
        return new self(
            $data['host'],
            $data['port'],
            $data['db'],
            $data['user'],
            $data['password'],
            @$data['start_ticket_id']
        );
    }
}

<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\DBAL\Event;

use Application\DeskPRO\DBAL\Connection;

class PostRollback extends \Doctrine\Common\EventArgs
{
    /**
     * @var \Application\DeskPRO\DBAL\Connection
     */
    protected $connection;

    /**
     * @param \Application\DeskPRO\DBAL\Connection $connection
     */
    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }
}

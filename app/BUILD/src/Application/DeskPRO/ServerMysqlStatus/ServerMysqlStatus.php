<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\ServerMysqlStatus;

use Application\DeskPRO\App;
use Doctrine\ORM\EntityManager;

class ServerMysqlStatus
{
    /**
     * @var \Doctrine\ORM\EntityManager
     */
    protected $em;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * @return array
     */
    public function getMysqlStatus()
    {
        return $this->_getInfo();
    }

    /**
     * @return array
     */
    protected function _getInfo()
    {
        try {
            $mysql_processes = App::getDb()->fetchAll('SHOW PROCESSLIST');
        } catch (\Exception $e) {
            $mysql_processes = null;
        }

        try {
            $mysql_status = App::getDb()->fetchAllKeyValue('SHOW STATUS', [], [], 0, 1);
        } catch (\Exception $e) {
            $mysql_status = null;
        }

        return [
            'mysql_processes' => $mysql_processes,
            'mysql_status'    => $mysql_status,
        ];
    }
}

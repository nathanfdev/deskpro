<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\ServerMysqlInfo;

use Application\DeskPRO\DBAL\Connection;
use Application\DeskPRO\ORM\Util\Util;

class ServerMysqlInfo
{
    /**
     * @var \Application\DeskPRO\DBAL\Connection
     */
    private $db;

    /**
     * @var \Doctrine\ORM\EntityManager[]
     */
    private $ems;

    /**
     * @param Connection                    $db
     * @param \Doctrine\ORM\EntityManager[] $ems
     */
    public function __construct(Connection $db, array $ems)
    {
        $this->db  = $db;
        $this->ems = $ems;
    }

    /**
     * @return array
     */
    public function getMysqlInfo()
    {
        return $this->db->fetchAllKeyValue('SHOW VARIABLES', [], [], 0, 1);
    }

    /**
     * @return string|null
     */
    public function getSchemaDiff()
    {
        $schema_diff = [];

        foreach ($this->ems as $id => $em) {
            $diff = Util::getUpdateSchemaSql($em);
            if ($diff) {
                $diff          = implode(";\n", $diff).';';
                $schema_diff[] = "/* DB Connection: $id */\n\n$diff";
            }
        }

        return implode("\n\n\n\n", $schema_diff);
    }
}

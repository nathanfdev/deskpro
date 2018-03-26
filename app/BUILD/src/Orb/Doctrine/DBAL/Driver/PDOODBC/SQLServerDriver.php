<?php

/**
 * Orb.
 */

namespace Orb\Doctrine\DBAL\Driver\PDOODBC;

class SQLServerDriver extends AbstractDriver
{
    /**
     * @throws \InvalidArgumentException
     *
     * @return \Doctrine\DBAL\Platforms\AbstractPlatform
     */
    public function getDatabasePlatform()
    {
        return new \Doctrine\DBAL\Platforms\SQLServerPlatform();
    }

    /**
     * @param \Doctrine\DBAL\Connection $conn
     *
     * @return \Doctrine\DBAL\Schema\AbstractSchemaManager|\Doctrine\DBAL\Schema\SQLServerSchemaManager
     */
    public function getSchemaManager(\Doctrine\DBAL\Connection $conn)
    {
        return new \Doctrine\DBAL\Schema\SQLServerSchemaManager($conn);
    }
}

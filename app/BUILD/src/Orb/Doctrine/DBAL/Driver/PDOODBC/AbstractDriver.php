<?php

/**
 * Orb.
 */

namespace Orb\Doctrine\DBAL\Driver\PDOODBC;

class AbstractDriver implements \Doctrine\DBAL\Driver
{
    /**
     * @param array       $params
     * @param string|null $username
     * @param string|null $password
     * @param array       $driverOptions
     *
     * @return \Doctrine\DBAL\Driver\Connection|Connection
     */
    public function connect(array $params, $username = null, $password = null, array $driverOptions = [])
    {
        return new Connection(
            $this->_constructPdoDsn($params),
            $username,
            $password,
            $driverOptions
        );
    }

    /**
     * Constructs the ODBC PDO DSN.
     *
     * @param array $params
     *
     * @return string The DSN
     */
    private function _constructPdoDsn(array $params)
    {
        $dsn = 'odbc:'.$params['dsn'];

        return $dsn;
    }

    /**
     * @throws \InvalidArgumentException
     *
     * @return \Doctrine\DBAL\Platforms\AbstractPlatform
     */
    public function getDatabasePlatform()
    {
        $classname         = $this->platform;
        $default_classname = 'Doctrine\\DBAL\\Platforms\\'.$this->platform;

        if (class_exists($default_classname)) {
            return new $default_classname();
        } elseif (class_exists($classname)) {
            return new $classname();
        } else {
            throw new \InvalidArgumentException();
        }
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

    /**
     * @return string
     */
    public function getName()
    {
        return 'pdo_odbc';
    }

    /**
     * @param \Doctrine\DBAL\Connection $conn
     *
     * @return string
     */
    public function getDatabase(\Doctrine\DBAL\Connection $conn)
    {
        $params = $conn->getParams();

        return $params['dbname'];
    }
}

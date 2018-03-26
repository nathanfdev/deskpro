<?php

namespace DeskPRO\Services\Lib;

/**
 * Class Database.
 */
class Database
{
    /**
     * @param array                                     $config
     * @param \Application\DeskPRO\DBAL\Connection|null $conn
     *
     * @return \Application\DeskPRO\DBAL\Connection|\Doctrine\DBAL\Connection
     */
    public static function getDbIfClosed(array $config, \Application\DeskPRO\DBAL\Connection $conn = null)
    {
        if (!$conn || !$conn->isConnected()) {
            return self::getDb($config);
        }

        try {
            $v = $conn->fetchColumn('SELECT 1');
            if ($v == 1) {
                return $conn;
            }
        } catch (\Exception $e) {
        }

        return self::getDb($config);
    }

    /**
     * @throws \Doctrine\DBAL\DBALException
     *
     * @return \Application\DeskPRO\DBAL\Connection
     */
    private static function getDb($config)
    {
        return \Doctrine\DBAL\DriverManager::getConnection(array_merge($config, [
            'driver'       => 'pdo_mysql',
            'wrapperClass' => 'Application\\DeskPRO\\DBAL\\Connection',
        ]));
    }
}

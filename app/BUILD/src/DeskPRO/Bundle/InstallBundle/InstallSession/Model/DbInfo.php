<?php

namespace DeskPRO\Bundle\InstallBundle\InstallSession\Model;

class DbInfo
{
    public $host;
    public $user;
    public $password;
    public $dbname;

    /**
     * @param bool $without_dbname Connect to the server withotu selecting the dbname
     *
     * @return \PDO
     */
    public function getPdo($without_dbname = false)
    {
        $conn_info = \DpRun\LowUtil::getMysqlInfoFromConfigArray([
            'host'     => $this->host,
            'user'     => $this->user,
            'password' => $this->password,
            'dbname'   => $without_dbname ? null : $this->dbname,
        ]);

        $pdo = new \PDO($conn_info['dsn'], $conn_info['user'], $conn_info['password']);
        $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

        return $pdo;
    }
}

<?php

namespace DpRun;

class LowUtil
{
    /**
     * Fetches MySQL connection info based on a DeskPRO config array. This is mainly
     * about parsing a 'host' string to detect when we need to set the port or if
     * we should use a unix socket.
     *
     * @param array $config
     *
     * @return array
     */
    public static function getMysqlInfoFromConfigArray(array $config)
    {
        // numeric index array, means an array of arrays,
        // choose one at random
        $chosenKey = null;
        if (isset($config[0]) && !isset($config['host'])) {
            $chosenKey = array_rand($config);
            $config = $config[$chosenKey];
        }

        $config = array_merge([
            'user'     => null,
            'password' => null,
            'host'     => null,
            'dbname'   => null
        ], $config);



        $info = [
            'unix_socket'  => null,
            'host'         => null,
            'port'         => 3306,
            'user'         => $config['user'],
            'password'     => $config['password'],
            'dbname'       => $config['dbname'],
            'dsn'          => null,
            'chosen_key'   => $chosenKey,
            'pdo_options'  => !empty($config['pdo_options']) ? $config['pdo_options'] : [],
            'doctrine'     => [
                'driver'        => 'pdo_mysql',
                'user'          => $config['user'],
                'password'      => $config['password'],
                'dbname'        => $config['dbname'] ?: null,
                'charset'       => 'utf8',
                'driverOptions' => !empty($config['pdo_options']) ? $config['pdo_options'] : [],
            ],
        ];

        if (substr($config['host'], 0, 12) === 'unix_socket:') {
            $info['unix_socket']  = substr($config['host'], 12);
            $info['dsn']          = "mysql:unix_socket={$info['unix_socket']}";
            $info['doctrine']['unix_socket'] = $info['unix_socket'];
        } elseif (preg_match('#^(.*?):([0-9]+)$#', $config['host'], $m)) {
            $info['host']         = $m[1];
            $info['port']         = $m[2];
            $info['dsn']          = "mysql:host={$info['host']};port={$info['port']}";
            $info['doctrine']['host'] = $info['host'];
            $info['doctrine']['port'] = $info['port'];
        } else {
            $info['host']         = $config['host'];
            $info['port']         = 3306;
            $info['dsn']          = "mysql:host={$info['host']};port={$info['port']}";
            $info['doctrine']['host'] = $info['host'];
            $info['doctrine']['port'] = $info['port'];
        }

        $info['dsn'] .= ';charset=utf8';

        if (isset($config['dbname'])) {
            $info['dsn'] .= ";dbname={$config['dbname']}";
        }

        return $info;
    }

    /**
     * Get a PDO connection from info array.
     *
     * @param array $conn_info Array of db config, or result of getMysqlInfoFromConfigArray()
     * @return \PDO
     */
    public static function getPdoFromMysqlInfo(array $conn_info)
    {
        if (!isset($conn_info['dsn'])) {
            $conn_info = self::getMysqlInfoFromConfigArray($conn_info);
        }

        $options = [
            \PDO::ATTR_ERRMODE            => \PDO::ERRMODE_EXCEPTION,
            \PDO::MYSQL_ATTR_INIT_COMMAND => 'SET sql_mode=\'\', NAMES utf8'
        ];

        if (!empty($conn_info['pdo_options'])) {
            $options = array_merge($options, $conn_info['pdo_options']);
        }

        $pdo = new \PDO(
            $conn_info['dsn'],
            $conn_info['user'],
            $conn_info['password'],
            $options
        );

        return $pdo;
    }

    public static function getMongoConfigFromArray(array $connInfo)
    {

        $defaults = [
            'server' => [
                'host' => 'localhost',
                'port' => '27017'
            ],
            'options' => []
        ];

        $config = [
            'server' => $connInfo,
            'options' => isset($connInfo['options']) ? $connInfo['options'] : []
        ];

        $config = array_replace_recursive($defaults, $config);

        if (substr($config['server']['host'], 0, 10) === 'mongodb://') {
            $config['server'] = $config['host'];
        } elseif (preg_match('#^(.*?):([0-9]+)$#', $config['server']['host'], $m)) {
            $config['server'] = "mongodb://{$m[1]}:{$m[1]}";
        } else {
            $config['server'] = "mongodb://{$config['server']['host']}:{$config['server']['port']}";
        }

        return $config;
    }
}

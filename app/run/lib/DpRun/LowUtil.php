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
            'doctrine'     => [
                'driver'     => 'pdo_mysql',
                'user'       => $config['user'],
                'password'   => $config['password'],
                'dbname'     => $config['dbname'] ?: null,
                'charset'    => 'utf8'
            ],
        ];

        if (substr($config['host'], 0, 12) === 'unix_socket:') {
            $info['unix_socket']  = substr($config['host'], 13);
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
}

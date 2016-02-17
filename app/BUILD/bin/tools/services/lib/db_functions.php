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

/**
 * @throws \Doctrine\DBAL\DBALException
 *
 * @return \Application\DeskPRO\DBAL\Connection
 */
function get_db()
{
    /* @var \DpRun\DpEnv $DP_ENV */
    global $DP_ENV;

    $db_info = \Dprun\LowUtil::getMysqlInfoFromConfigArray($DP_ENV->getConfig('database'));

    $connectionParams = array(
        'driver'       => 'pdo_mysql',
        'host'         => $db_info['host'],
        'port'         => $db_info['port'],
        'user'         => $db_info['user'],
        'password'     => $db_info['password'],
        'dbname'       => $db_info['dbname'],
        'wrapperClass' => 'Application\\DeskPRO\\DBAL\\Connection',
    );
    $conn = \Doctrine\DBAL\DriverManager::getConnection($connectionParams);

    return $conn;
}

/**
 * @param \Application\DeskPRO\DBAL\Connection|null $conn
 *
 * @return \Application\DeskPRO\DBAL\Connection|\Doctrine\DBAL\Connection
 */
function get_db_if_closed(\Application\DeskPRO\DBAL\Connection $conn = null)
{
    if (!$conn || !$conn->isConnected()) {
        return get_db();
    }

    try {
        $v = $conn->fetchColumn('SELECT 1');
        if ($v == 1) {
            return $conn;
        }
    } catch (\Exception $e) {
    }

    return get_db();
}

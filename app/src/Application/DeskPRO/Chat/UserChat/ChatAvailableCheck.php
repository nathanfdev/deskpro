<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/  |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2014, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at https://www.deskpro.com/eula/                            |
|                                                                          |
| By using this software, you acknowledge having read the license          |
| and agree to be bound thereby.                                           |
|                                                                          |
| Please note that DeskPRO is not free software. We release the full       |
| source code for our software because we trust our users to pay us for    |
| the huge investment in time and energy that has gone into both creating  |
| this software and supporting our customers. By providing the source code |
| we preserve our customers' ability to modify, audit and learn from our   |
| work. We have been developing DeskPRO since 2001, please help us make it |
| another decade.                                                          |
|                                                                          |
| Like the work you see? Think you could make it better? We are always     |
| looking for great developers to join us: http://www.deskpro.com/jobs/    |
|                                                                          |
| ~ Thanks, Everyone at Team DeskPRO                                       |
\**************************************************************************/

/**
 * DeskPRO
 *
 * @package DeskPRO
 */

namespace Application\DeskPRO\Chat\UserChat;

use Application\DeskPRO\App;

/**
 * Low-level chat available checker used mainly in serve_dp to decide if the
 * chat widget should display.
 */
class ChatAvailableCheck
{
    /**
     * @var int
     */
    private static $available_time = null;

    /**
     * @return int
     */
    public static function getAvailableTime()
    {
        if (self::$available_time !== null) {
            return self::$available_time;
        }

        if (defined('DPC_IS_CLOUD')) {
            return self::getAvailableTimeCloud();
        }

        $online_time = 0;
        if (file_exists(dp_get_data_dir().'/chat_is_available.trigger')) {
            $online_time = file_get_contents(dp_get_data_dir().'/chat_is_available.trigger');
        }

        self::$available_time = (int) $online_time;

        return self::$available_time;
    }

    /**
     * @return int
     */
    private static function getAvailableTimeCloud()
    {
        // Cached files
        $trigger_file      = dp_get_data_dir().'/chat_is_available.cloud.trigger';
        $trigger_file_time = dp_get_data_dir().'/chat_is_available.cloud.time';

        if (file_exists($trigger_file) && file_exists($trigger_file_time)) {
            $time = intval(@file_get_contents($trigger_file_time));
            if ($time && (time() - $time) < 6) {
                self::$available_time = intval(@file_get_contents($trigger_file));
            }
        }

        // Do query
        if (self::$available_time === null) {
            $sql  = "
                SELECT UNIX_TIMESTAMP(CONVERT_TZ(sessions.date_last, '+00:00', @@session.time_zone))
                FROM sessions
                LEFT JOIN people ON (people.id = sessions.person_id)
                WHERE sessions.date_last >= ? AND sessions.is_chat_available = 1 AND people.is_agent = 1
                ORDER BY sessions.id DESC
                LIMIT 1
            ";
            $sql_params = array(date('Y-m-d H:i:s', time() - 20));

            // Set during serve_dp.php, the low-level chat script
            if (isset($GLOBALS['DP_DB_PDO'])) {
                $pdo = $GLOBALS['DP_DB_PDO'];

                $q = $pdo->prepare($sql);
                $q->execute($sql_params);

                self::$available_time = (int) $q->fetchColumn();

            // Otherwise use the normal connection
            } elseif (class_exists('DeskPRO\\App', false)) {
                $db = App::$container->getDb();

                self::$available_time = (int) $db->fetchColumn($sql, $sql_params);
            }

            @file_put_contents($trigger_file, self::$available_time);
            @file_put_contents($trigger_file_time, time());
        }

        return self::$available_time;
    }
}

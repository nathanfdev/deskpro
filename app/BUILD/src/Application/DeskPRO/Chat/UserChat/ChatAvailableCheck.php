<?php

/**
 * DeskPRO.
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
        if (file_exists(App::$container->getParameter('dp.user.cache_dir').'/chat_is_available.trigger')) {
            $online_time = file_get_contents(App::$container->getParameter('dp.user.cache_dir').'/chat_is_available.trigger');
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
        $trigger_file      = App::$container->getParameter('dp.user.cache_dir').'/chat_is_available.cloud.trigger';
        $trigger_file_time = App::$container->getParameter('dp.user.cache_dir').'/chat_is_available.cloud.time';

        if (file_exists($trigger_file) && file_exists($trigger_file_time)) {
            $time = intval(@file_get_contents($trigger_file_time));
            if ($time && (time() - $time) < 6) {
                self::$available_time = intval(@file_get_contents($trigger_file));
            }
        }

        // Do query
        if (self::$available_time === null) {
            $sql = "
                SELECT UNIX_TIMESTAMP(CONVERT_TZ(sessions.date_last, '+00:00', @@session.time_zone))
                FROM sessions
                LEFT JOIN people ON (people.id = sessions.person_id)
                WHERE sessions.date_last >= ? AND sessions.is_chat_available = 1 AND people.is_agent = 1
                ORDER BY sessions.id DESC
                LIMIT 1
            ";
            $sql_params = [date('Y-m-d H:i:s', time() - 20)];

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

<?php

namespace Application\DeskPRO\Util;

use DpSys\LowError\SystemErrorHandler;

/**
 * Simple util that counts iterations in a loop and will log an exception
 * when too many iterations happen. Generally used to prevent infinite loops.
 */
class InfLoopAssert
{
    private static $counters = [];

    /**
     * @param mixed $id
     *
     * @return string
     */
    private static function getId($id)
    {
        if (is_array($id)) {
            $parts = [];
            foreach ($id as $p) {
                $parts[] = self::getId($id);
            }

            return implode('_', $id);
        } elseif (is_object($id)) {
            return spl_object_hash($id);
        } else {
            return $id;
        }
    }

    /**
     * Reset a counter.
     *
     * @param string $id
     */
    public static function reset($id)
    {
        self::$counters[self::getId($id)] = 0;
    }

    /**
     * Count a loop iteration.
     *
     * @param string $id
     * @param int    $max
     * @param string $msg
     * @param bool   $throw
     *
     * @return bool
     */
    public static function count($id, $max, $msg, $throw = false)
    {
        $id = self::getId($id);

        if (!isset(self::$counters[$id])) {
            self::$counters[$id] = 0;
        }

        ++self::$counters[$id];

        if (self::$counters[$id] > $max) {
            if (is_callable($msg)) {
                $msg = call_user_func($msg, $id, $max);
            }
            $e = new \RuntimeException($msg);
            if ($throw) {
                throw $e;
            }

            SystemErrorHandler::logException($e, true, "inf_loop_$id");

            return false;
        }

        return true;
    }
}

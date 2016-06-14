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

namespace Application\DeskPRO\Util;

use DeskPRO\Kernel\KernelErrorHandler;

/**
 * Simple util that counts iterations in a loop and will log an exception
 * when too many iterations happen. Generally used to prevent infinite loops.
 */
class InfLoopAssert
{
    private static $counters = array();

    /**
     * @param mixed $id
     *
     * @return string
     */
    private static function getId($id)
    {
        if (is_array($id)) {
            $parts = array();
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

            KernelErrorHandler::logException($e, true, "inf_loop_$id");

            return false;
        }

        return true;
    }
}

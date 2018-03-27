<?php

/**
 * DeskPRO.
 */

namespace DpBehat\E2E;

use DpBehat\BaseContext;

/**
 * When E2E tests are running, the main DeskPRO config needs to read from the test config rather
 * than the normal config (/app/BUILD/tests/config). See \DpRun\DpEnv.
 */
class E2EContext extends BaseContext
{
    private static $trigger_file_path;

    /**
     * @BeforeSuite
     */
    public static function enableTestMode()
    {
        /* \DpRun\DpEnv */
        global $DP_ENV;

        self::$trigger_file_path = $DP_ENV->getDpRoot().DIRECTORY_SEPARATOR.'/config/e2e_running.trigger';

        touch(self::$trigger_file_path);
        if (!file_exists(self::$trigger_file_path)) {
            throw new \RuntimeException('Failed to touch: '.self::$trigger_file_path);
        }
    }

    /**
     * @AfterSuite
     */
    public static function disableTestMode()
    {
        if (self::$trigger_file_path) {
            unlink(self::$trigger_file_path);
            if (file_exists(self::$trigger_file_path)) {
                throw new \RuntimeException('Failed unlink: '.self::$trigger_file_path);
            }
        }
    }
}

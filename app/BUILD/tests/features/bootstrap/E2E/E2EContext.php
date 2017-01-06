<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2017, DeskPRO Ltd.
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

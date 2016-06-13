<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2016, DeskPRO Ltd.
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

namespace DeskPRO\Component\Util;

class ExceptionUtils
{
    private function __construct()
    {
    }

    /**
     * Use this to detect a suppressed error. E.g., if you are doing
     * file operations you usually want to suppress an error to prevent it being
     * written to screen. However, you often will want the actual error
     * message so you can log it. This  makes that a bit easier.
     *
     * @param callable $run_fn    The function to run
     * @param array    $errorInfo Error info will be placed into this array
     *
     * @throws $ex If $run_fn throws, this will throw
     *
     * @return mixed
     *
     * @todo Hmm, doens't actually appear to work?!
     */
    public static function detectSuppressedError($run_fn, &$errorInfo)
    {
        $old = error_reporting(E_ALL);

        // This causes a suppressed error that we
        // know about, so we can test if the $run_fn
        // has it's own error
        $x = @$dp_clear_last_error;

        $ret = null;
        $ex  = null;
        try {
            $ret = call_user_func($run_fn);
        } catch (\Exception $e) {
            $ex = $e;
        }

        error_reporting($old);

        $einfo = error_get_last();
        if ($einfo && strpos($einfo['message'], 'dp_clear_last_error') !== false) {
            // The last error was our own marker, so we can ignore it
            $einfo = null;
        }

        $errorInfo = $einfo;

        if ($ex) {
            throw $ex;
        }

        return $ret;
    }
}

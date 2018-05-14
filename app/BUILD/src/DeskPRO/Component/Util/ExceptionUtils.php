<?php

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
        set_error_handler(function () {
        }, E_ALL);

        // This causes a suppressed error that we
        // know about, so we can test if the $run_fn
        // has it's own error
        $x = @$dp_clear_last_error;

        restore_error_handler();

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

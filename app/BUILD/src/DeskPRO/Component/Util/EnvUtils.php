<?php

namespace DeskPRO\Component\Util;

class EnvUtils
{
    private function __construct()
    {
    }

    /**
     * True if the current OS is Windows.
     *
     * @return bool
     */
    public static function isWindows()
    {
        return strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';
    }
}

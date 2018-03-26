<?php

namespace DpSys\CodePlugin;

class DpPlugins
{
    private function __construct()
    {
        // static class
    }

    /**
     * @var CodePluginManager
     */
    private static $inst;

    /**
     * @return CodePluginManager
     */
    public static function getManager()
    {
        if (!self::$inst) {
            self::$inst = new CodePluginManager();
        }

        return self::$inst;
    }
}

<?php

namespace Application\DeskPRO\Log;

/**
 * Class UndefinedSettingsLogger
 *
 * @package Application\DeskPRO\Log
 */
class UndefinedSettingsLogger extends \Orb\Log\Logger
{
    /**
     * Path to settings.php
     */
    const SETTINGS_PATH = __DIR__.'/../../../../sys/config/settings.php';

    /**
     * @var string[]|null
     */
    private static $keys = null;

    /**
     * @var bool[]
     */
    private static $processed = [];

    /**
     * Log if setting was not found in settings.php
     *
     * @param string $key
     * @param string $level
     */
    public function logIfNotInSettings($key, $level = 'logWarn')
    {
        if (isset(self::$processed[$key])) {
            return;
        }

        if (!in_array($key, self::getSettingsKeys())) {
            $this->{$level}(sprintf('"%s" is not defined in %s', $key, basename(self::SETTINGS_PATH)));
        }

        self::$processed[$key] = true;
    }

    /**
     * @return array|null
     */
    private static function getSettingsKeys()
    {
        if (is_array(self::$keys)) {
            return self::$keys;
        }

        return self::$keys = array_keys(require self::SETTINGS_PATH);
    }
}

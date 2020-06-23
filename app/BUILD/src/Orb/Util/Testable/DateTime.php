<?php
namespace Orb\Util\Testable;

/**
 * DateTime Classs
 *
 * In code should be transparently used as regular DateTime class
 * In tests could be mocked with setTimestampState static method
 */
class DateTime extends \DateTime
{
    /**
     * @var null
     */
    protected static $timestamp = null;

    /**
     * Constructor
     *
     * @param string $time
     * @param \DateTimeZone $timezone
     */
    public function __construct($time = 'now', \DateTimeZone $timezone = null)
    {
        parent::__construct($time, $timezone);

        if (!is_null(self::$timestamp)) {
            $this->setTimestamp(self::$timestamp);
        }
    }

    /**
     * Should be used only in tests!
     *
     * Set timestamp (used for unit tests)
     *
     * @param integer $time
     */
    public static function setTimestampState($time)
    {
        self::$timestamp = $time;
    }

    /**
     * Should be used only in tests!
     *
     * Unset timestamp (used for unit tests)
     */
    public static function unsetTimestampState()
    {
        self::$timestamp = null;
    }
}
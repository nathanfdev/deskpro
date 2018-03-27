<?php

/**
 * Orb.
 */

namespace Orb\Log\Filter;

use Orb\Log\LogItem;

/**
 * This formats the 'line' value of an item with other properties. This gives
 * a single, suitable value that can be written to a flat file for example.
 */
class SimpleLineFormatter extends \Orb\Filter\AbstractFilter
{
    const DEFAULT_FORMAT      = '[%datetime% %priority_name%] %message%';
    const DEFAULT_TIME_FORMAT = 'Y-m-d H:i:s';

    /**
     * @var string
     */
    protected $_format;

    /**
     * @var string
     */
    protected $_time_format;

    /**
     * @var bool
     */
    protected $_has_time = false;

    public function __construct($format = null, $time_format = self::DEFAULT_TIME_FORMAT)
    {
        $this->_format      = $format;
        $this->_time_format = $time_format;
        if ($format === null || strpos($format, '%datetime%') !== false) {
            $this->_has_time = true;
        }
    }

    public function filter($log_item)
    {
        if (!$log_item) {
            return;
        }

        $message_line = $this->_format;

        if ($this->_format === null) { // micro-opt for default format
            $datetime     = $log_item['datetime']->format($this->_time_format);
            $message_line = "[{$datetime} {$log_item['priority_name']}] {$log_item['message']}";
        } else {
            foreach ($log_item as $k => $v) {
                if ($this->_has_time && $v instanceof \DateTime) {
                    $v = $v->format($this->_time_format);
                }

                if (is_scalar($v)) {
                    $message_line = str_replace("%$k%", $v, $message_line);
                }
            }
        }

        $log_item[LogItem::MESSAGE_LINE] = $message_line;

        return $log_item;
    }
}

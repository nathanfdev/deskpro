<?php

/**
 * Orb.
 */

namespace Orb\Log\Writer;

use Orb\Log\Logger;
use Orb\Log\LogItem;

/**
 * This writer just writes using error_log.
 */
class Syslog extends AbstractWriter
{
    private $ident;

    public function __construct($ident)
    {
        $this->addFilter(new \Orb\Log\Filter\SimpleLineFormatter());
        $this->ident = $ident;
    }

    /**
     * Write a message to the log.
     */
    public function _write(LogItem $log_item)
    {
        @openlog($this->ident, 0, \LOG_USER);
        $msg = $log_item[LogItem::MESSAGE_LINE];

        switch ($log_item->getPriority()) {
            case Logger::EMERG:
                $level = \LOG_EMERG;
                break;
            case Logger::ALERT:
                $level = \LOG_ALERT;
                break;
            case Logger::CRIT:
                $level = \LOG_CRIT;
                break;
            case Logger::ERR:
                $level = \LOG_ERR;
                break;
            case Logger::WARN:
                $level = \LOG_WARNING;
                break;
            case Logger::NOTICE:
                $level = \LOG_NOTICE;
                break;
            case Logger::INFO:
                $level = \LOG_INFO;
                break;
            case Logger::DEBUG:
                $level = \LOG_DEBUG;
                break;
            case Logger::STRICT:
                $level = \LOG_NOTICE;
                break;
            default:
                $level = \LOG_ERR;
        }

        @syslog($level, $msg);
        @closelog();
    }
}

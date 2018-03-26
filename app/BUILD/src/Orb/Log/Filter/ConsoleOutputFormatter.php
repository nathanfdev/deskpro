<?php

/**
 * Orb.
 */

namespace Orb\Log\Filter;

use Orb\Log\Logger;
use Orb\Log\LogItem;

/**
 * This wraps the message in console outputer formatting tags depending on its
 * priority.
 */
class ConsoleOutputFormatter extends \Orb\Filter\AbstractFilter
{
    public function filter($log_item)
    {
        if (!$log_item) {
            return;
        }

        $message = isset($log_item[LogItem::MESSAGE_LINE]) ? $log_item[LogItem::MESSAGE_LINE] : $log_item[LogItem::MESSAGE];

        switch ($log_item[LogItem::PRIORITY]) {
            case Logger::ERR:
            case Logger::WARN:
            case Logger::CRIT:
            case Logger::EMERG:
            case Logger::ALERT:
                $message = '<error>'.$message.'</error>';
                break;

            case Logger::NOTICE:
                $message = '<info>'.$message.'</info>';
                break;

            case Logger::DEBUG:
                $message = '<comment>'.$message.'</comment>';
                break;
        }

        $log_item[LogItem::MESSAGE_LINE] = $message;

        return $log_item;
    }
}

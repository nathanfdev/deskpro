<?php

/**
 * Orb.
 */

namespace Orb\Log\Filter;

use Orb\Log\LogItem;

/**
 * This filter detends '--'s at the beginning of a message to denote an
 * "indentation" of a message. It will trim the dashes, and set a new 'indent'
 * value on the log item.
 */
class IndentFilter extends \Orb\Filter\AbstractFilter
{
    public function filter($log_item)
    {
        if (!$log_item) {
            return;
        }

        $message = $log_item[LogItem::MESSAGE];
        $m       = null;
        if (preg_match('#^((\-\-)+)#', $message, $m)) {
            $len     = strlen($m[1]);
            $indent  = $len / 2;
            $message = trim(substr($message, $len));

            $log_item['indent']         = $indent;
            $log_item[LogItem::MESSAGE] = $message;
        }

        return $log_item;
    }
}

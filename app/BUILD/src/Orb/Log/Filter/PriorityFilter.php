<?php

/**
 * Orb.
 */

namespace Orb\Log\Filter;

use Orb\Log\Logger;
use Orb\Log\LogItem;

/**
 * This filter fitlers out log events whose priority is below a certain level.
 */
class PriorityFilter extends \Orb\Filter\AbstractFilter
{
    /** @var int */
    protected $min_level = Logger::WARN;

    public function __construct($min_level)
    {
        $this->min_level = $min_level;
    }

    public function filter($log_item)
    {
        if (!$log_item) {
            return;
        }

        if (isset($log_item['ignore_pri_filter'])) {
            unset($log_item['ignore_pri_filter']);

            return $log_item;
        }

        if ($log_item[LogItem::PRIORITY] > $this->min_level) {
            return;
        }

        return $log_item;
    }
}

<?php

/**
 * Orb.
 */

namespace Orb\Log\Filter;

/**
 * This wraps up a function callback.
 */
class CallbackFormatter extends \Orb\Filter\AbstractFilter
{
    /** @var callable */
    protected $callback;

    public function __construct($callback)
    {
        $this->callback = $callback;
    }

    public function filter($log_item)
    {
        if (!$log_item) {
            return;
        }

        $log_item = call_user_func($this->callback, $log_item);

        return $log_item;
    }
}

<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\AppBundle\Util;

/**
 * Simple timer using microtime(true).
 *
 * Timer starts on construction and will give you back the elapsed time when asked.
 */
class SimpleTimer
{
    protected $start_time;

    public function __construct()
    {
        $this->start_time = microtime(true);
    }

    /**
     * The microtime difference since object instantiation.
     *
     * @return float
     */
    public function getElapsedTime()
    {
        return microtime(true) - $this->start_time;
    }
}

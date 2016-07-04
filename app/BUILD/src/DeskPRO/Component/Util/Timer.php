<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2016, DeskPRO Ltd.
 *
 * The license agreement under which this software is released
 * can be found at https://www.deskpro.com/eula/
 *
 * By using this software, you acknowledge having read the license
 * and agree to be bound thereby.
 *
 * Please note that DeskPRO is not free software. We release the full
 * source code for our software because we trust our users to pay us for
 * the huge investment in time and energy that has gone into both creating
 * this software and supporting our customers. By providing the source code
 * we preserve our customers' ability to modify, audit and learn from our
 * work. We have been developing DeskPRO since 2001, please help us make it
 * another decade.
 *
 * Like the work you see? Think you could make it better? We are always
 * looking for great developers to join us: http://www.deskpro.com/jobs/
 *
 * ~ Thanks, Everyone at Team DeskPRO
 */

namespace DeskPRO\Component\Util;

class Timer
{
    const START = '@start';
    const END   = '@end';

    /**
     * @var array
     */
    private $ticks = [];

    private function __construct()
    {
        $this->ticks[self::START] = microtime(true);
    }

    /**
     * @return Timer
     */
    public static function start()
    {
        return new self();
    }

    /**
     * @param string $name Give the tick a name
     *
     * @return $this
     */
    public function tick($name = null)
    {
        if ($name === null) {
            $name = 'time_'.count($this->ticks);
        }

        $this->ticks[$name] = microtime(true);

        return $this;
    }

    /**
     * @param string $name
     *
     * @return bool
     */
    public function hasTick($name)
    {
        return isset($this->ticks[$name]);
    }

    /**
     * @return $this
     */
    public function end()
    {
        $this->tick(self::END);

        return $this;
    }

    /**
     * @param string|null $tick
     *
     * @return float
     */
    public function getTime($tick = null)
    {
        if ($tick === null) {
            $ts = microtime(true);
        } else {
            if (!isset($this->ticks[$tick])) {
                throw new \InvalidArgumentException("Unknown tick: $tick");
            }
            $ts = $this->ticks[$tick];
        }

        return $ts - $this->ticks[self::START];
    }

    /**
     * @param string|null $tickA
     * @param string|null $tickB
     *
     * @return float
     */
    public function getTimeBetween($tickA, $tickB = null)
    {
        if ($tickA === null) {
            $tsA = microtime(true);
        } else {
            if (!isset($this->ticks[$tickA])) {
                throw new \InvalidArgumentException("Unknown tick: $tickA");
            }
            $tsA = $this->ticks[$tickA];
        }

        if ($tickB === null) {
            $tsB = microtime(true);
        } else {
            if (!isset($this->ticks[$tickB])) {
                throw new \InvalidArgumentException("Unknown tick: $tickB");
            }
            $tsB = $this->ticks[$tickB];
        }

        if ($tsA > $tsB) {
            return $tsA - $tsB;
        } else {
            return $tsB - $tsA;
        }
    }

    /**
     * @param string|null $tickA
     * @param string|null $tickB
     * @param string      $format
     *
     * @return string
     */
    public function formatTimeBetween($tickA, $tickB, $format = '%.3fs')
    {
        return sprintf($format, $this->getTimeBetween($tickA, $tickB));
    }

    /**
     * @param string|null $tick
     * @param string      $format
     *
     * @return string
     */
    public function formatTime($tick = null, $format = '%.3fs')
    {
        return sprintf($format, $this->getTime($tick));
    }

    /**
     * Gets total time from start to now. This will end
     * the timer if it isn't already.
     *
     * @return float
     */
    public function getTotalTime()
    {
        if (!$this->hasTick(self::END)) {
            $this->tick(self::END);
        }

        return $this->getTime(self::END);
    }

    /**
     * @param string $format
     *
     * @return string
     */
    public function formatTotalTime($format = '%.3fs')
    {
        return sprintf($format, $this->getTotalTime());
    }
}

<?php

namespace DeskPRO\Component\Util;

class Timer
{
    const START = '@start';
    const END   = '@end';
    const LAST  = '@last';

    /**
     * @var array
     */
    private $ticks = [];

    /**
     * @var string
     */
    private $lastTick = null;

    private function __construct()
    {
        $this->ticks[self::START] = microtime(true);
        $this->lastTick           = self::START;
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
        $this->lastTick     = $name;

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
     * @param string $name
     *
     * @return int
     */
    public function getTick($name)
    {
        if ($name === self::LAST) {
            return $this->getTick($this->lastTick);
        }

        return isset($this->ticks[$name]) ? $this->ticks[$name] : 0;
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
     * Get the time since $tick (last tick if unspecified).
     *
     * @param string|null $sinceTick
     *
     * @return float
     */
    public function getTimeSinceTick($sinceTick = self::LAST)
    {
        $ts = microtime(true);

        return $ts - $this->getTick($sinceTick);
    }

    /**
     * Get the time between $forTick and the one before it.
     *
     * @param string $forTick
     *
     * @return float
     */
    public function getTime($forTick = self::LAST)
    {
        if ($forTick === self::LAST) {
            $forTick = $this->lastTick;
        }

        $names = array_keys($this->ticks);
        $idx   = array_search($forTick, $names, true);

        if (!$idx || empty($names[$idx - 1])) {
            return 0.0;
        }

        return $this->getTimeBetween($names[$idx - 1], $names[$idx]);
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
     * @param string|null $forTick
     * @param string      $format
     *
     * @return string
     */
    public function formatTime($forTick = null, $format = '%.3fs')
    {
        return sprintf($format, $this->getTime($forTick));
    }

    /**
     * @param string|null $sinceTick
     * @param string      $format
     *
     * @return string
     */
    public function formatTimeSincetick($sinceTick = null, $format = '%.3fs')
    {
        return sprintf($format, $this->getTimeSinceTick($sinceTick));
    }

    /**
     * Gets total time from start to end. This will end
     * the timer if it isn't already.
     *
     * @return float
     */
    public function getTotalTime()
    {
        if (!$this->hasTick(self::END)) {
            $this->tick(self::END);
        }

        return $this->getTimeBetween(self::START, self::END);
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

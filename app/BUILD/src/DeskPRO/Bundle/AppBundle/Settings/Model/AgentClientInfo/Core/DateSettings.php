<?php

namespace DeskPRO\Bundle\AppBundle\Settings\Model\AgentClientInfo\Core;

/**
 * Class DateSettings.
 */
class DateSettings
{
    /**
     * @var string
     */
    private $fullTime;

    /**
     * @var string
     */
    private $full;

    /**
     * @var string
     */
    private $day;

    /**
     * @var string
     */
    private $dayShort;

    /**
     * @var string
     */
    private $time;

    /**
     * @var bool
     */
    private $disableRelativeTimes;

    /**
     * @return bool
     */
    public function getDisableRelativeTimes()
    {
        return $this->disableRelativeTimes;
    }

    /**
     * @param bool $disableRelativeTimes
     *
     * @return $this
     */
    public function setDisableRelativeTimes($disableRelativeTimes)
    {
        $this->disableRelativeTimes = $disableRelativeTimes;

        return $this;
    }

    /**
     * @return string
     */
    public function getFullTime()
    {
        return $this->fullTime;
    }

    /**
     * @param string $fullTime
     *
     * @return $this
     */
    public function setFullTime($fullTime)
    {
        $this->fullTime = $fullTime;

        return $this;
    }

    /**
     * @return string
     */
    public function getFull()
    {
        return $this->full;
    }

    /**
     * @param string $full
     *
     * @return $this
     */
    public function setFull($full)
    {
        $this->full = $full;

        return $this;
    }

    /**
     * @return string
     */
    public function getDay()
    {
        return $this->day;
    }

    /**
     * @param string $day
     *
     * @return $this
     */
    public function setDay($day)
    {
        $this->day = $day;

        return $this;
    }

    /**
     * @return string
     */
    public function getDayShort()
    {
        return $this->dayShort;
    }

    /**
     * @param string $dayShort
     *
     * @return $this
     */
    public function setDayShort($dayShort)
    {
        $this->dayShort = $dayShort;

        return $this;
    }

    /**
     * @return string
     */
    public function getTime()
    {
        return $this->time;
    }

    /**
     * @param string $time
     *
     * @return $this
     */
    public function setTime($time)
    {
        $this->time = $time;

        return $this;
    }
}

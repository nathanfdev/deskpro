<?php

namespace DeskPRO\Bundle\AppBundle\Settings\Model;

use JMS\Serializer\Annotation as JMS;

class UpdaterSettings
{
    /**
     * @var bool
     * @JMS\Type("boolean")
     */
    private $isEnabled;

    /**
     * @var int
     * @JMS\Type("integer")
     */
    private $intervalDays;

    /**
     * @var int
     * @JMS\Type("string")
     */
    private $timeOfDay;

    /**
     * @var \DateTimeZone
     * @JMS\Type("to_string<DateTimeZone>")
     */
    private $timezone;

    /**
     * @return bool
     */
    public function isEnabled()
    {
        return $this->isEnabled;
    }

    /**
     * @param bool $isEnabled
     *
     * @return $this
     */
    public function setIsEnabled($isEnabled)
    {
        $this->isEnabled = $isEnabled;

        return $this;
    }

    /**
     * @return int
     */
    public function getIntervalDays()
    {
        return $this->intervalDays;
    }

    /**
     * @param int $intervalDays
     *
     * @return $this
     */
    public function setIntervalDays($intervalDays)
    {
        $this->intervalDays = $intervalDays;

        return $this;
    }

    /**
     * @return int
     */
    public function getTimeOfDay()
    {
        return $this->timeOfDay;
    }

    /**
     * @param int $timeOfDay
     *
     * @return $this
     */
    public function setTimeOfDay($timeOfDay)
    {
        $this->timeOfDay = $timeOfDay;

        return $this;
    }

    /**
     * @return array
     */
    public function getTimeOfDayParts()
    {
        list($h, $m) = explode(':', $this->getTimeOfDay());

        return [
            'hour'   => $h,
            'minute' => $m,
        ];
    }

    /**
     * @return \DateTimeZone
     */
    public function getTimezone()
    {
        return $this->timezone;
    }

    /**
     * @param \DateTimeZone|string $timezone
     *
     * @return $this
     */
    public function setTimezone($timezone)
    {
        if (!$timezone instanceof \DateTimeZone) {
            $timezone = new \DateTimeZone($timezone);
        }
        $this->timezone = $timezone;

        return $this;
    }

    /**
     * @return \DateTime|null
     */
    public function calculateNextTimeUtc()
    {
        if (!$this->isEnabled()) {
            return;
        }

        $tz = $this->getTimezone();

        $date = new \DateTime('now', $tz);
        $tod  = $this->getTimeOfDayParts();
        $date->setTime($tod['hour'], $tod['minute'], 0);
        $date->modify('+'.$this->getIntervalDays() * 24 .' hours');

        $date->setTimezone(new \DateTimeZone('UTC'));

        return $date;
    }
}

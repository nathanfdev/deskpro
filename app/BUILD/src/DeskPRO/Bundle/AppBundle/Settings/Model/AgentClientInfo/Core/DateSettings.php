<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2017, DeskPRO Ltd.
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

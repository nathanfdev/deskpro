<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/  |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2014, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at https://www.deskpro.com/eula/                            |
|                                                                          |
| By using this software, you acknowledge having read the license          |
| and agree to be bound thereby.                                           |
|                                                                          |
| Please note that DeskPRO is not free software. We release the full       |
| source code for our software because we trust our users to pay us for    |
| the huge investment in time and energy that has gone into both creating  |
| this software and supporting our customers. By providing the source code |
| we preserve our customers' ability to modify, audit and learn from our   |
| work. We have been developing DeskPRO since 2001, please help us make it |
| another decade.                                                          |
|                                                                          |
| Like the work you see? Think you could make it better? We are always     |
| looking for great developers to join us: http://www.deskpro.com/jobs/    |
|                                                                          |
| ~ Thanks, Everyone at Team DeskPRO                                       |
\**************************************************************************/

/**
 * Orb
 *
 * @package Orb
 * @category Util
 */

namespace Orb\Util;

/**
 * Utility class to work with a set of work hours/days/holidays
 * to calculate time lengths and thresholds.
 */
class WorkHoursSet implements WorkHoursInterface
{
    /**
     * When the work day starts. This is stored as the number of seconds after 00:00:00.
     *
     * @var integer
     */
    protected $work_start;

    /**
     * When the work day ends. This is stored as the number of seconds after 00:00:00.
     *
     * @var integer
     */
    protected $work_end;

    /**
     * Array of work days, stored with keys corresponding to day numbers.
     * 0 = Sunday, 6 = Saturday (same as PHP, easy to convert to MySQL which is 1 = Sunday, 7 = Saturday)
     *
     * A true value means the day is on. E.g., m-f is: array(false, true, true, true, true, true, false)
     *
     * NOTE: This array is stored differently than it is passed into the constructor. Read constructor
     * doc for usage.
     *
     * @var array
     */
    protected $work_days = array();

    /**
     * Timezone for work hours/days to be considered in
     *
     * @var string
     */
    protected $work_timezone;

    /**
     * List of work holidays
     *
     * @var array
     */
    protected $work_holidays = array();


    /**
     * @param int   $work_start         Seconds into the day when work day starts
     * @param int   $work_end           Seconds into the day when work day ends
     * @param array $work_days          Array of days of week. 0 = Sunday, 6 = Saturday. Eg: array(1,3,5) is Monday, Wed, Friday
     * @param int   $work_timezone      Timezone string for the hours
     * @param array $work_holidays      Array of holidays
     */
    public function __construct($work_start, $work_end, array $work_days, $work_timezone, array $work_holidays = array())
    {
        try {
            $tz = new \DateTimeZone($work_timezone);
            if (!$tz) {
                $work_timezone = 'UTC';
            }
        } catch (\Exception $e) {
            $work_timezone = 'UTC';
        }

        // Converts array of day numbers into
        // an array of dow=>true/false
        $work_days_array = array_fill(0, 6, false);
        foreach ($work_days as $k) {
            if (isset($work_days_array[$k])) {
                $work_days_array[$k] = true;
            }
        }

        $any = false;
        foreach ($work_days_array as $v) {
            if ($v) {
                $any = true;
                break;
            }
        }

        if (!$any) {
            $work_days_array = array(true, true, true, true, true, true, true);
        }

        $this->work_start = $work_start;
        $this->work_end = $work_end;
        $this->work_days = $work_days_array;
        $this->work_timezone = $work_timezone;
        $this->work_holidays = $work_holidays;

        if ($this->work_start > $this->work_end) {
            $tmp = $this->work_start;
            $this->work_start = $this->work_end;
            $this->work_end = $tmp;
        }
    }


    /**
     * @param  \DateTime $date_start
     * @param  int       $delay
     * @return \DateTime
     */
    public function calculateWorkHoursDelay(\DateTime $date_start, $delay)
    {
        $work_day_length = $this->work_end - $this->work_start;
        if ($work_day_length <= 0) {
            return null;
        }

        if ($delay < 0) {
            return $this->_calculateWorkHoursDelayPast($date_start, $delay);
        }

        $date_end = new \DateTime('@' . $date_start->getTimestamp());
        if ($this->work_timezone) {
            $date_end->setTimezone(new \DateTimeZone($this->work_timezone));
        }

        $time_remaining = null;
        if ($this->isInWorkDay($date_end, $time_remaining)) {
            if ($delay > $time_remaining) {
                $date_end->modify('+' . ($time_remaining + 1) . ' seconds');
                $delay -= $time_remaining;
            } else {
                $date_end->modify('+' . $delay . ' seconds');
                $delay = 0;
            }
        }

        while ($delay > 0) {
            $date_end = $this->getNextWorkDayStart($date_end);
            if ($delay > $work_day_length) {
                $date_end->modify('+' . ($work_day_length + 1) . ' seconds');
                $delay -= $work_day_length;
            } else {
                $date_end->modify('+' . $delay . ' seconds');
                $delay = 0;
            }
        }

        // Some bad configurations (eg no work days) could cause bad
        // dates, so this prevents an error below
        if (!$date_end || !$date_end->getTimestamp()) {
            return null;
        }

        return new \DateTime('@' . $date_end->getTimestamp());
    }


    /**
     * @param  \DateTime      $date_start
     * @param  int            $delay
     * @return \DateTime|null
     */
    private function _calculateWorkHoursDelayPast(\DateTime $date_start, $delay)
    {
        $work_day_length = $this->work_end - $this->work_start;
        if ($work_day_length <= 0) {
            return null;
        }

        $date_end = new \DateTime('@' . $date_start->getTimestamp());
        if ($this->work_timezone) {
            $date_end->setTimezone(new \DateTimeZone($this->work_timezone));
        }

        $time_remaining = null;
        if ($this->isInWorkDay($date_end, $time_remaining)) {
            $time_past = $work_day_length - $time_remaining;
            $date_end->modify('-' . ($time_past + 1) . ' seconds');
            $delay += $time_past;
        }

        while ($delay < 0) {
            $date_end = $this->getNextWorkDayStart($date_end, true);
            $delay += $work_day_length;
        }

        return $this->calculateWorkHoursDelay($date_end, $delay);
    }


    /**
     * @param  \DateTime $date
     * @param  int|null  $time_remaining
     * @return bool
     */
    public function isInWorkDay(\DateTime $date, &$time_remaining = null)
    {
        $time_remaining = null;

        list($dow, $year, $month, $day, $hours, $minutes, $seconds) = explode('|', $date->format('w|Y|n|j|G|i|s'));
        $dow = intval($dow);
        $year = intval($year);
        $month = intval($month);
        $day = intval($day);
        $hours = intval($hours);
        $minutes = intval($minutes);
        $seconds = intval($seconds);

        if (!isset($this->work_days[$dow]) || !$this->work_days[$dow]) {
            return false;
        }

        $day_offset = $hours * 3600 + $minutes * 60 + $seconds;
        if ($day_offset < $this->work_start || $day_offset > $this->work_end) {
            return false;
        }

        foreach ($this->work_holidays AS $holiday) {
            if ($holiday['year'] && $year != $holiday['year']) {
                continue;
            }

            if ($holiday['day'] == $day && $holiday['month'] == $month) {
                return false;
            }
        }

        $time_remaining = $this->work_end - $day_offset;

        return true;
    }


    /**
     * @param  \DateTime $date
     * @param  bool      $backwards
     * @return \DateTime
     */
    public function getNextWorkDayStart(\DateTime $date, $backwards = false)
    {
        $work_date = clone $date;
        $adjust = ($backwards ? '-1 day' : '+1 day');

        $has_adjusted = false;

        do {
            list($dow, $year, $month, $day, $hours, $minutes, $seconds) = explode('|', $work_date->format('w|Y|n|j|G|i|s'));
            $dow = intval($dow);
            $year = intval($year);
            $month = intval($month);
            $day = intval($day);
            $hours = intval($hours);
            $minutes = intval($minutes);
            $seconds = intval($seconds);

            if (!isset($this->work_days[$dow]) || !$this->work_days[$dow]) {
                $work_date->modify($adjust);
                $work_date->setTime(0, 0, 0);
                $has_adjusted = true;
                continue;
            }

            foreach ($this->work_holidays AS $holiday) {
                // is today a holiday?
                if ($holiday['year'] && $year != $holiday['year']) {
                    continue;
                }

                if ($holiday['day'] == $day && $holiday['month'] == $month) {
                    $work_date->modify($adjust);
                    $work_date->setTime(0, 0, 0);
                    $has_adjusted = true;
                    continue 2;
                }
            }

            $day_offset = $hours * 3600 + $minutes * 60 + $seconds;
            if ($day_offset >= $this->work_start && !$has_adjusted) {
                $work_date->modify($adjust);
                $work_date->setTime(0, 0, 0);
                $has_adjusted = true;
                continue;
            }

            // today is a work day and we haven't passed the start, so shift to that
            $work_date->setTime($this->getWorkStartHour(), $this->getWorkStartMinute());
            break;
        } while (true);

        return $work_date;
    }

    /**
     * @param  int|\DateTime      $start
     * @param  int|\DateTime|null $end
     * @return int
     */
    public function getWorkTimeBetween($start, $end = null)
    {
        $start = ($start instanceof \DateTime ? $start->getTimestamp() : intval($start));
        $end = ($end instanceof \DateTime ? $end->getTimestamp() : intval($end));

        if (!$end) {
            $end = time();
        }

        $length = $end - $start;
        $work_day_length = $this->work_end - $this->work_start;

        $date = new \DateTime("@$start");
        if ($this->work_timezone) {
            $date->setTimezone(new \DateTimeZone($this->work_timezone));
        }

        $wait_time = 0;

        $time_remaining = null;
        if ($this->isInWorkDay($date, $time_remaining)) {
            if ($length <= $time_remaining) {
                // waiting happened entirely in this work day
                $wait_time += $length;

                return $wait_time;
            } else {
                $wait_time += $time_remaining;
                $date->modify('+' . ($time_remaining + 1) . ' seconds');
            }
        } while ($date->getTimestamp() < $end) {
            $date = $this->getNextWorkDayStart($date);
            if ($date->getTimestamp() >= $end) {
                break;
            }

            $work_end = $date->getTimestamp() + $work_day_length;
            if ($work_end >= $end) {
                // waiting ended within a work day
                $wait_time += $end - $date->getTimestamp();
                break;
            } else {
                // work day ended, still waiting from beginning
                $wait_time += $work_day_length;
                $date->modify('+' . ($work_day_length + 1) . ' seconds');
            }
        }

        return $wait_time;
    }

    /**
     * @return int
     */
    public function getWorkStart()
    {
        return $this->work_start;
    }

    /**
     * @return int
     */
    public function getWorkStartHour()
    {
        return floor($this->work_start / 3600);
    }

    /**
     * @return int
     */
    public function getWorkStartMinute()
    {
        return floor(($this->work_start % 3600) / 60);
    }

    /**
     * @return int
     */
    public function getWorkEnd()
    {
        return $this->work_end;
    }

    /**
     * @return int
     */
    public function getWorkEndHour()
    {
        return floor($this->work_end / 3600);
    }

    /**
     * @return int
     */
    public function getWorkEndMinute()
    {
        return floor(($this->work_end % 3600) / 60);
    }

    /**
     * @return int
     */
    public function getWorkDays()
    {
        return $this->work_days;
    }

    /**
     * @return int
     */
    public function getWorkTimezone()
    {
        return $this->work_timezone;
    }

    /**
     * @return array
     */
    public function getWorkHolidays()
    {
        return $this->work_holidays;
    }

    /**
     * @return int
     */
    public function getSecondsPerDay()
    {
        return $this->work_end - $this->work_start;
    }

    /**
     * @return int
     */
    public function getSecondsPerWeek()
    {
        return count($this->work_days) * $this->getSecondsPerDay();
    }
}

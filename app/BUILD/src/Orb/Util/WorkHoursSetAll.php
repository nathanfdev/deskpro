<?php

/**
 * Orb.
 *
 * @category Util
 */

namespace Orb\Util;

class WorkHoursSetAll implements WorkHoursInterface
{
    /**
     * @param \DateTime $date_start
     * @param int       $delay
     *
     * @return \DateTime
     */
    public function calculateWorkHoursDelay(\DateTime $date_start, $delay)
    {
        $date = $date_start->getTimestamp() + $delay;

        return new \DateTime("@$date");
    }

    /**
     * @param \DateTime $date
     * @param int|null  $time_remaining
     *
     * @return bool
     */
    public function isInWorkDay(\DateTime $date, &$time_remaining = null)
    {
        return true;
    }

    /**
     * @param \DateTime $date
     * @param bool      $backwards
     *
     * @return \DateTime
     */
    public function getNextWorkDayStart(\DateTime $date, $backwards = false)
    {
        $work_date = clone $date;
        $adjust    = ($backwards ? '-1 day' : '+1 day');

        $work_date->modify($adjust);
        $work_date->setTime(0, 0, 0);

        return $work_date;
    }
    
    /**
     * e.g. if wh are 9am-6pm and it's currently 10am, then return 10am + 1sec
     *      if it's midnight, then the next time would be tomorrow at 9am
     *
     * @param \DateTime $_date
     * @return \DateTime
     */
    public function getNextWorkTimeStart(\DateTime $_date)
    {
        $date = new \DateTime('@'.$_date->getTimestamp());
        $date->modify('+1 seconds');
        return $date;
    }

    /**
     * @param int|\DateTime      $start
     * @param int|\DateTime|null $end
     *
     * @return int
     */
    public function getWorkTimeBetween($start, $end = null)
    {
        if (!$end) {
            $end = time();
        }

        $start = ($start instanceof \DateTime ? $start->getTimestamp() : intval($start));
        $end   = ($end instanceof \DateTime ? $end->getTimestamp() : intval($end));

        return $end - $start;
    }
}

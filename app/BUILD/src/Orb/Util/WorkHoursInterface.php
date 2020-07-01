<?php

/**
 * Orb.
 *
 * @category Util
 */

namespace Orb\Util;

interface WorkHoursInterface
{
    /**
     * @param \DateTime $date_start
     * @param int       $delay
     *
     * @return \DateTime
     */
    public function calculateWorkHoursDelay(\DateTime $date_start, $delay);

    /**
     * @param \DateTime $date
     * @param int|null  $time_remaining
     *
     * @return bool
     */
    public function isInWorkDay(\DateTime $date, &$time_remaining = null);

    /**
     * @param \DateTime $date
     * @param bool      $backwards
     *
     * @return \DateTime
     */
    public function getNextWorkDayStart(\DateTime $date, $backwards = false);

    /**
     * e.g. if wh are 9am-6pm and it's currently 10am, then return 10am + 1sec
     *      if it's midnight, then the next time would be tomorrow at 9am
     *
     * @param \DateTime $date
     * @return \DateTime
     */
    public function getNextWorkTimeStart(\DateTime $date);

    /**
     * @param int|\DateTime      $start
     * @param int|\DateTime|null $end
     *
     * @return int
     */
    public function getWorkTimeBetween($start, $end = null);
}

<?php

namespace DpTest\Orb\Util;

use Orb\Util\WorkHoursSet;

class WorkHoursSetTest extends \PHPUnit_Framework_TestCase
{
    /**
     * `isInWorkDay` doesn't do any timezone conversion. We should take into account timezone ourselves
     *
     * @testWith    ["Europe/London", "2020-04-14T18:30:00+01:00", false, null]
     *              ["Europe/London", "2020-04-14T17:30:00+01:00", true, 1800]
     *              ["Europe/London", "2020-04-14T08:59:59+01:00", false, null]
     *              ["Europe/London", "2020-04-14T09:00:00+01:00", true, 32400]
     *
     * @param string $timezone
     * @param string $date - Date in UTC
     * @param bool $expectedResult
     * @param int $expectedRemaining
     */
    public function testIsInWorkDay($timezone, $date, $expectedResult, $expectedRemaining)
    {
        // GIVEN
        $wh = $this->getStandardWorkHoursSet($timezone);
        $remaining = null;
        $date = new \DateTime($date);

        // WHEN
        $res = $wh->isInWorkDay($date, $remaining);

        // THEN
        $this->assertEquals($expectedResult, $res);
        $this->assertEquals($expectedRemaining, $remaining);
    }

    /**
     * @testWith    ["Europe/London", "2020-04-14 17:30:00", "2020-04-14 18:30:00", 0]
     *              ["Europe/London", "2020-04-14 17:00:00", "2020-04-14 18:00:00", 0]
     *              ["Europe/London", "2020-04-14 16:30:00", "2020-04-14 18:00:00", 1800]
     *              ["Europe/London", "2020-04-14 16:00:00", "2020-04-15 08:00:00", 3600]
     *              ["Europe/London", "2020-04-14 16:00:00", "2020-04-15 09:00:00", 7200]
     *              ["UTC",           "2020-04-14 16:00:00", "2020-04-14 18:30:00", 7200]
     *              ["UTC",           "2020-04-14 16:00:00", "2020-04-19 18:30:00", 104400]
     *
     * @param string $timezone
     * @param \DateTime $dateStart - in UTC
     * @param \DateTime $dateEnd - in UTC
     * @param int $expectedResult
     */
    public function testGetWorkTimeBetween($timezone, $dateStart, $dateEnd, $expectedResult)
    {
        // GIVEN
        $wh = $this->getStandardWorkHoursSet($timezone);
        $dateStart = new \DateTime($dateStart);
        $dateEnd = new \DateTime($dateEnd);

        // WHEN
        $res = $wh->getWorkTimeBetween($dateStart, $dateEnd);

        // THEN
        $this->assertEquals($expectedResult, $res);
    }

    /**
     * `getNextWorkDayStart` doesn't do any timezone conversion. We should take into account timezone ourselves
     *
     * @testWith    ["Europe/London", "2020-04-14T12:00:00+01:00", "2020-04-15T09:00:00+01:00"]
     *
     * @param string $timezone
     * @param string $date - in UTC
     * @param string $expectedDate - in UTC
     */
    public function testGetNextWorkDayStart($timezone, $date, $expectedDate)
    {
        // GIVEN
        $wh = $this->getStandardWorkHoursSet($timezone);
        $date = new \DateTime($date);
        $expectedDate = new \DateTime($expectedDate);

        // WHEN
        $res = $wh->getNextWorkDayStart($date);

        // THEN
        $this->assertEquals($expectedDate->format(\DateTime::RFC3339), $res->format(\DateTime::RFC3339));
    }

    /**
     * @testWith    ["Europe/London", "2020-04-14 07:00:00", "2020-04-14 08:00:00"]
     *              ["Europe/London", "2020-04-14 08:00:00", "2020-04-14 08:00:01"]
     *              ["Europe/London", "2020-04-14 09:00:00", "2020-04-14 09:00:01"]
     *              ["Europe/London", "2020-04-14 12:00:00", "2020-04-14 12:00:01"]
     *              ["Europe/London", "2020-04-14 17:00:00", "2020-04-15 08:00:00"]
     *              ["Europe/London", "2020-04-14 17:00:01", "2020-04-15 08:00:00"]
     *              ["Europe/London", "2020-04-14 17:30:00", "2020-04-15 08:00:00"]
     *              ["Europe/London", "2020-04-17 17:00:01", "2020-04-20 08:00:00"]
     *              ["UTC",           "2020-04-14 07:00:00", "2020-04-14 09:00:00"]
     *              ["UTC",           "2020-04-14 12:00:00", "2020-04-14 12:00:01"]
     *              ["UTC",           "2020-04-14 18:00:00", "2020-04-15 09:00:00"]
     *              ["UTC",           "2020-04-17 18:00:01", "2020-04-20 09:00:00"]
     *
     * @param string $timezone
     * @param string $date - in UTC
     * @param string $expectedDate - in UTC
     */
    public function testGetNextWorkTimeStart($timezone, $date, $expectedDate)
    {
        // GIVEN
        $wh = $this->getStandardWorkHoursSet($timezone);
        $date = new \DateTime($date);
        $expectedDate = new \DateTime($expectedDate);

        // WHEN
        $res = $wh->getNextWorkTimeStart($date);

        // THEN
        $this->assertEquals($expectedDate->format(\DateTime::RFC3339), $res->format(\DateTime::RFC3339));
    }

    /**
     *
     * @return WorkHoursSet
     */
    protected function getStandardWorkHoursSet($timezone = 'UTC')
    {
        return new WorkHoursSet(
            9 * 3600,
            18 * 3600,
            [1, 2, 3, 4, 5],
            $timezone,
            []
        );
    }
}

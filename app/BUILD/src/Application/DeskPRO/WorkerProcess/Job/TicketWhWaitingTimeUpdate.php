<?php

namespace Application\DeskPRO\WorkerProcess\Job;

use Application\DeskPRO\Entity\Setting;
use Orb\Util\WorkHoursInterface;
use Orb\Util\WorkHoursSetAll;

/**
 * Handles ticket
 *  `total_user_waiting_wh_start`,
 *  `total_user_waiting_wh`,
 *  `total_user_waiting_wh`
 * fields update
 */
class TicketWhWaitingTimeUpdate extends AbstractJob
{
    const DEFAULT_INTERVAL = 60;

    const LAST_CALC_SETTING = 'last_wh_calc';

    /**
     * @var WorkHoursInterface
     */
    private $wh;

    /**
     * @var array
     */
    private $lastCalcData;

    /**
     * {@inheritdoc}
     */
    protected function init()
    {
        $this->wh = $this->getContainer()->get('work_hours_set_factory')->create();
        $this->printWorkHoursSet();

        $this->lastCalcData = $this->getContainer()->getSetting(self::LAST_CALC_SETTING);
        if ($this->lastCalcData) {
            $this->lastCalcData = @json_decode($this->lastCalcData, true);
        }
        if (!is_array($this->lastCalcData)) {
            $this->lastCalcData = [];
        }
        $this->printLastCalcData();
    }

    /**
     * {@inheritdoc}
     */
    public function run()
    {
        $now = new \DateTime();

        $dayStart = new \DateTime();
        $dayEnd   = new \DateTime();

        if ($this->wh instanceof WorkHoursSetAll) {
            $dayStart->setTime(0, 0);
            $dayEnd->setTime(23, 59, 59);
        } else {
            $dayStart->setTimezone(new \DateTimeZone($this->wh->getWorkTimezone()));
            $dayStart->setTime($this->wh->getWorkStartHour(), $this->wh->getWorkStartMinute());
            $dayEnd->setTimezone(new \DateTimeZone($this->wh->getWorkTimezone()));
            $dayEnd->setTime($this->wh->getWorkEndHour(), $this->wh->getWorkEndMinute());

            // convert to UTC
            $dayStart->setTimezone(new \DateTimeZone('UTC'));
            $dayEnd->setTimezone(new \DateTimeZone('UTC'));
        }

        $this->logger->logInfo('Curr day start: '.$dayStart->format('Y-m-d H:i:s'));
        $this->logger->logInfo('Curr day end: '.$dayEnd->format('Y-m-d H:i:s'));
        $this->logger->logInfo('Now: '.$now->format('Y-m-d H:i:s'));

        /**
         * Get day start/end used by previous run
         * We need this to catch new day change (in case if WH = all day)
         * and use previous day as calculation bases
         */
        $lastRunDayStart = isset($this->lastCalcData['day_start'])
            ? new \DateTime($this->lastCalcData['day_start'])
            : $dayStart;
        $lastRunDayEnd = isset($this->lastCalcData['day_end'])
            ? new \DateTime($this->lastCalcData['day_end'])
            : $dayEnd;
        $lastCalcDate = isset($this->lastCalcData['calc_date'])
            ? new \DateTime($this->lastCalcData['calc_date'])
            : new \DateTime('last year');

        if (($lastCalcDate < $lastRunDayEnd) && ($lastRunDayEnd < $now)) {
            $this->logger->logInfo("Going to update end of the day calculation!");

            $this->getContainer()->get('doctrine')->getConnection()->executeUpdate('
                UPDATE tickets
                SET
                    # add the time between when wh started counting, and the end of the day
                    total_user_waiting_wh = (
                        total_user_waiting_wh
                        + (UNIX_TIMESTAMP(:dayEnd) - UNIX_TIMESTAMP(total_user_waiting_wh_start))
                    ),

                    # update the datettime to the next work time
                    total_user_waiting_wh_start = :nextWhStartTime
                WHERE
                    status IN (\'awaiting_agent\', \'pending\')
                AND total_user_waiting_wh_start BETWEEN :dayStart AND :dayEnd',
            [
                'dayStart'        => $lastRunDayStart->format('Y-m-d H:i:s'),
                'dayEnd'          => $lastRunDayEnd->format('Y-m-d H:i:s'),
                'nextWhStartTime' => $this->wh->getNextWorkTimeStart($lastRunDayEnd)->format('Y-m-d H:i:s'),
            ]);

            $this->lastCalcData['calc_date'] = $now->format('Y-m-d H:i:s');
        } else {
            $this->logger->logInfo("The day is still going on or calculations has been already done.");
        }

        $this->lastCalcData['day_start'] = $dayStart->format('Y-m-d H:i:s');
        $this->lastCalcData['day_end']   = $dayEnd->format('Y-m-d H:i:s');

        $this->saveLastCalcData();
    }

    protected function saveLastCalcData()
    {
        $this->getContainer()->getEm()->getRepository(Setting::class)
            ->updateSetting(self::LAST_CALC_SETTING, json_encode($this->lastCalcData));
    }

    protected function printLastCalcData()
    {
        $this->logger->logInfo("Last calc time: "
            .(isset($this->lastCalcData['calc_date'])
                ? (new \DateTime($this->lastCalcData['calc_date']))->format('Y-m-d H:i:s')
                : 'not set'));
        $this->logger->logInfo("Last run day start: "
            .(isset($this->lastCalcData['day_start'])
                ? (new \DateTime($this->lastCalcData['day_start']))->format('Y-m-d H:i:s')
                : 'not set'));
        $this->logger->logInfo("Last run day end: "
            .(isset($this->lastCalcData['day_end'])
                ? (new \DateTime($this->lastCalcData['day_end']))->format('Y-m-d H:i:s')
                : 'not set'));
    }

    protected function printWorkHoursSet()
    {
        if ($this->wh instanceof WorkHoursSetAll) {
            $this->logger->logInfo('Work hours set: WorkHoursSetAll');
        } else {
            $this->logger->logInfo(sprintf(
                'Work hours set: Start: %02s:%02s. End: %02s:%02s. Days: %s. Timezone: %s',
                $this->wh->getWorkStartHour(),
                $this->wh->getWorkStartMinute(),
                $this->wh->getWorkEndHour(),
                $this->wh->getWorkEndMinute(),
                implode(',', array_map(function ($day) {
                    return $day ? 1 : 0;
                }, $this->wh->getWorkDays())),
                $this->wh->getWorkTimezone()
            ));
        }
    }
}

<?php

namespace DeskPRO\Bundle\AppBundle\Ticket;

use DeskPRO\Bundle\AppBundle\Settings\BrandAwareSettingsResolver;
use DeskPRO\Component\Util\UnserializeUtil;
use Orb\Util\WorkHoursSet;
use Orb\Util\WorkHoursSetAll;
use Orb\Util\Arrays;
use Orb\Util\OptionsArray;

class WorkHoursSetFactory
{
    /**
     *
     * @var BrandAwareSettingsResolver
     */
    protected $settingResolver;

    public function __construct(BrandAwareSettingsResolver $settingResolver)
    {
        $this->settingResolver = $settingResolver;
    }

    /**
     * Return WorhHoursSet instantiated from `core_tickets.work_hours` setting
     * and customized with parameters (if passed)
     *
     * @param string $start     - time string, like '09:00'
     * @param string $end       - time string, like '18:00'
     * @param string $days      - Array of days of days (1 = monday, 7 = sunday)
     * @param string $timezone
     * @return WorkHoursSet|WorkHoursSetAll
     */
    public function create(string $start = null, string $end = null, string $days = null, string $timezone = null)
    {
        $inputWorkHours = [];
        if ($start) {
            list($h, $m) = explode(":", $start);
            if (!is_numeric($h) || !is_numeric($m)) {
                throw new \InvalidArgumentException('Invalid `start` argument');
            }
            $inputWorkHours['start_hour'] = $h;
            $inputWorkHours['start_min'] = $m;
        }
        if ($end) {
            list($h, $m) = explode(":", $end);
            if (!is_numeric($h) || !is_numeric($m)) {
                throw new InvalidArgumentException('Invalid `end` argument');
            }
            $inputWorkHours['end_hour'] = $h;
            $inputWorkHours['end_min'] = $m;
        }
        if ($days) {
            $inputWorkHours['work_days'] = explode(",", $days);
            foreach ($inputWorkHours['work_days'] as &$day) {
                $day = trim($day);
                if (!is_numeric($day)) {
                    throw new InvalidArgumentException('Invalid `days` argument');
                }
            }
        }
        if ($timezone) {
            $inputWorkHours['timezone'] = $timezone;
        }

        try {
            $work_hours = $this->settingResolver->getSetting('core_tickets.work_hours');
            if ($work_hours && !is_array($work_hours)) {
                try {
                    $work_hours = UnserializeUtil::unserializeArray($work_hours);
                } catch (\Exception $e) {
                    $work_hours = null;
                }
            }
            if ($work_hours) {
                $work_hours = Arrays::removeEmptyArray($work_hours);
                $work_hours = Arrays::removeNull($work_hours);
                $work_hours = Arrays::removeEmptyString($work_hours);
                $work_hours = array_merge($work_hours, $inputWorkHours);
                $work_hours = new OptionsArray($work_hours);
            } else if ($inputWorkHours) {
                $work_hours = new OptionsArray($inputWorkHours);
            }

            if ($work_hours) {
                return new WorkHoursSet(
                    $work_hours->get('start_hour', 9) * 3600 + $work_hours->get('start_min', 0) * 60,
                    $work_hours->get('end_hour', 18) * 3600 + $work_hours->get('end_min', 0) * 60,
                    $work_hours->get('work_days', [1, 2, 3, 4, 5]),
                    $work_hours->get('timezone', 'UTC'),
                    $work_hours->get('holidays', [])
                );
            } else {
                return new WorkHoursSetAll();
            }
        } catch (\Exception $e) {
            return new WorkHoursSetAll();
        }
    }
}

<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2016, DeskPRO Ltd.
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

/**
 * DeskPRO.
 */

namespace Application\InstallBundle\Upgrade\Build;

class Build1433777718 extends AbstractBuild
{
    public function run()
    {
        $db = $this->container->getDb();

        $slas = $db->fetchAllKeyValue("SELECT id, work_days FROM slas WHERE work_days != ''");
        foreach ($slas as $id => $days) {
            $days = explode(',', $days ?: '');
            $days = $this->getFixedDaysArray($days);
            $days = implode(',', $days);
            $db->update('slas', ['work_days' => $days], ['id' => $id]);
        }

        $default_wh = $db->fetchColumn("SELECT value FROM settings WHERE name = 'core_tickets.work_hours'");
        if ($default_wh) {
            $default_wh = @unserialize($default_wh);
        }
        if ($default_wh && @$default_wh['work_days']) {
            $default_wh['work_days'] = $this->getFixedDaysArray($default_wh['work_days'], true);
            $db->update('settings', ['value' => serialize($default_wh)], ['name' => 'core_tickets.work_hours']);
        }
    }

    /**
     * Fix settings where sunday is saved as 0 (PHP's 'w' format char) instead of 7 (ISO 'N' format char).
     *
     * @param array $bad_days
     * @param bool  $is_setting
     *
     * @return array
     */
    private function getFixedDaysArray(array $bad_days, $is_setting = false)
    {
        $days = [];

        if ($is_setting) {
            if (count($bad_days) === 7) {
                $days = [];
                foreach ($bad_days as $n => $onoff) {
                    if ($onoff) {
                        if ($n === 0) {
                            $days[] = 7;
                        } else {
                            $days[] = $n + 1;
                        }
                    }
                }
            }
        }

        foreach ($bad_days as $d) {
            $d = (int) $d;
            if ($d === 0) {
                $d = 7;
            }
            $days[] = $d;
        }

        $days = array_unique($days);
        sort($days);

        return $days;
    }
}

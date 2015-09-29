<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2015, DeskPRO Ltd.
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
namespace DeskPRO\Bundle\AppBundle\Data;

/**
 * Class DatePeriods.
 */
abstract class DatePeriods
{
    /**
     * @var array Period names
     */
    public static $names = ['today', 'yesterday', 'this_week', 'this_month', 'last_month', 'this_year', 'ever'];

    /**
     * Get period select DQL clause for a given target field.
     *
     * @param string $targetField
     *
     * @return string
     */
    public static function getDatePeriodCaseWhenDql($targetField)
    {
        $today               = date('Y-m-d', strtotime('today'));
        $yesterday           = date('Y-m-d', strtotime('yesterday'));
        $firstDayOfThisWeek  = date('Y-m-d', strtotime('monday this week'));
        $firstDayOfThisMonth = date('Y-m-d', strtotime('first day of this month'));
        $firstDayOfLastMonth = date('Y-m-d', strtotime('first day of -1 month'));
        $firstDayOfThisYear  = date('Y-01-01');

        $target = "DATE($targetField)";

        $groupSelectDql = "(CASE
            WHEN $target  = '$today' THEN 'today'
            WHEN $target  = '$yesterday' THEN 'yesterday'
            WHEN $target >= '$firstDayOfThisWeek' THEN 'this_week'
            WHEN $target >= '$firstDayOfThisMonth' THEN 'this_month'
            WHEN $target >= '$firstDayOfLastMonth' THEN 'last_month'
            WHEN $target >= '$firstDayOfThisYear' THEN 'this_year'
            ELSE 'ever'
        END)";

        return $groupSelectDql;
    }
}

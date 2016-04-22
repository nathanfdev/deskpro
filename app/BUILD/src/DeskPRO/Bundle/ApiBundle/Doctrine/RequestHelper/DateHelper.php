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

namespace DeskPRO\Bundle\ApiBundle\Doctrine\RequestHelper;

/**
 * Class DateHelper.
 */
class DateHelper
{
    /**
     * @var array Period names
     */
    public static $datePeriodLabels = [
        'today'      => 'Today',
        'yesterday'  => 'Yesterday',
        'this_week'  => 'This Week',
        'this_month' => 'This Month',
        'last_month' => 'Last Month',
        'this_year'  => 'This Year',
        'ever'       => 'Ever',
    ];

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

    /**
     * @param RequestQueryContext $context
     * @param string              $property
     * @param string              $minQueryParam
     * @param string              $maxQueryParam
     */
    public static function applyDateRangeFilter(RequestQueryContext $context, $property, $minQueryParam, $maxQueryParam)
    {
        $qb      = $context->getQb();
        $alias   = $context->getAlias();
        $request = $context->getRequest();

        $minValue = $request->get($minQueryParam);
        if ($minValue) {
            $qb->andWhere("$alias.$property >= DATE(:$minQueryParam)");
            $qb->setParameter($minQueryParam, $minValue);
        }

        $maxValue = $request->get($maxQueryParam);
        if ($maxValue) {
            $qb->andWhere("$alias.$property <= DATE(:$maxQueryParam)");
            $qb->setParameter($maxQueryParam, $maxValue);
        }
    }

    /**
     * @param RequestQueryContext $context
     * @param string              $property
     * @param string              $queryParam
     */
    public static function applyDatePeriodFilter(RequestQueryContext $context, $property, $queryParam)
    {
        $value = $context->getRequest()->get($queryParam);
        if (!$value) {
            return;
        }

        $periodDql = self::getDatePeriodCaseWhenDql("{$context->getAlias()}.$property");

        $context->getQb()->andWhere("$periodDql = :$queryParam");
        $context->getQb()->setParameter($queryParam, $value);
    }

    /**
     * @param RequestQueryContext $context
     * @param string              $property
     */
    public static function applyDatePeriodGroupBy(RequestQueryContext $context, $property)
    {
        $qb    = $context->getQb();
        $alias = $context->getAlias();

        if (is_array($property)) {
            $property = sprintf('COALESCE(%s)', implode(',', array_map(function ($property) use ($alias) {
                return "$alias.$property";
            }, $property)));
        } else {
            $property = "$alias.$property";
        }

        $periodDql = self::getDatePeriodCaseWhenDql($property);

        $qb->addSelect("$periodDql as group_name");
        $qb->addSelect("$periodDql as title");
        $qb->addSelect("FIELD($periodDql, 'today', 'yesterday', 'this_month', 'last_month', 'this_year', 'ever') as HIDDEN group_order");
        $qb->groupBy('group_name');
        $qb->orderBy('group_order');
    }
}

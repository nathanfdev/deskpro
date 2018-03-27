<?php

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
     * @param string $format      Could be 'date' or 'timestamp'
     *
     * @return string
     */
    public static function getDatePeriodCaseWhenDql($targetField, $format = 'date')
    {
        $today               = strtotime('today');
        $yesterday           = strtotime('yesterday');
        $firstDayOfThisWeek  = strtotime('monday this week');
        $firstDayOfThisMonth = strtotime('first day of this month');
        $firstDayOfLastMonth = strtotime('first day of -1 month');
        $firstDayOfThisYear  = strtotime('first day of January '.date('Y'));

        $target = $targetField;

        if ($format === 'date') {
            $today               = date('Y-m-d', strtotime('today'));
            $yesterday           = date('Y-m-d', strtotime('yesterday'));
            $firstDayOfThisWeek  = date('Y-m-d', strtotime('monday this week'));
            $firstDayOfThisMonth = date('Y-m-d', strtotime('first day of this month'));
            $firstDayOfLastMonth = date('Y-m-d', strtotime('first day of -1 month'));
            $firstDayOfThisYear  = date('Y-m-d', $firstDayOfThisYear);

            $target = "DATE($targetField)";
        }

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
            $qb->andWhere("$alias.$property >= DATE(:$alias$minQueryParam)");
            $qb->setParameter($alias.$minQueryParam, $minValue);
        }

        $maxValue = $request->get($maxQueryParam);
        if ($maxValue) {
            $qb->andWhere("$alias.$property <= DATE(:$alias$maxQueryParam)");
            $qb->setParameter($alias.$maxQueryParam, $maxValue);
        }
    }

    /**
     * @param RequestQueryContext $context
     * @param string              $property
     * @param string              $queryParam
     * @param string              $format
     */
    public static function applyDatePeriodFilter(RequestQueryContext $context, $property, $queryParam, $format = 'date')
    {
        $alias = $context->getAlias();
        $value = $context->getRequest()->get($queryParam);
        if (!$value) {
            return;
        }

        $periodDql = self::getDatePeriodCaseWhenDql("$alias.$property", $format);

        $context->getQb()->andWhere("$periodDql = :$alias$queryParam");
        $context->getQb()->setParameter($alias.$queryParam, $value);
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
        $qb->addSelect("$periodDql as date_title");
        $qb->addSelect("FIELD($periodDql, 'today', 'yesterday', 'this_month', 'last_month', 'this_year', 'ever') as HIDDEN group_order");
        $qb->groupBy('group_name');
        $qb->orderBy('group_order');
    }
}

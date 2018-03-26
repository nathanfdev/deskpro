<?php

namespace DeskPRO\Bundle\ReportBundle\Dpql2\Plugin\Hierarchy;

use DeskPRO\Bundle\ReportBundle\Dpql2\DpqlException;
use DeskPRO\Bundle\ReportBundle\Dpql2\SqlSelect;

/**
 * Class Hierarchy.
 */
class Hierarchy
{
    private static $hierarchicalTables = [
        'departments', 'organizations', 'products', 'custom_field_definition',
        'article_categories', 'download_categories', 'feedback_categories', 'news_categories', 'ticket_categories',
    ];

    /**
     * @param bool      $forceHierarchy
     * @param SqlSelect $sql
     *
     * @return bool
     */
    public static function isHierarchical(SqlSelect $sql, $forceHierarchy)
    {
        $isGrouping          = count($sql->getGroupBy()) >= 1;
        $isSimpleGrouping    = count($sql->getGroupBy()) === 1;
        $groupingTargetTable = self::getGroupingTargetTable($sql);

        if ((($isGrouping && $forceHierarchy) || $isSimpleGrouping) && (
                in_array($groupingTargetTable, self::$hierarchicalTables)
                || (is_string($groupingTargetTable) && strpos($groupingTargetTable, 'custom_data_') === 0)
                || (is_string($groupingTargetTable) && strpos($groupingTargetTable, 'custom_def_') === 0)
            )) {
            return true;
        }
        if (in_array($sql->getTable(), self::$hierarchicalTables)) {
            return true;
        }

        return false;
    }

    /**
     * @param SqlSelect $sql
     *
     * @throws DpqlException
     *
     * @return string|null
     */
    public static function getGroupingTargetTableReference(SqlSelect $sql)
    {
        if (!$table = self::getGroupingTargetTable($sql)) {
            return;
        }

        if ($table === $sql->getTable()) {
            return $table;
        } else {
            $joins = $sql->getGroupBy();
            if (!count($joins)) {
                throw new DpqlException('Cannot resolve grouping table alias (no joins)');
            }
            preg_match('/`(.+)`\.`(.+)`/isU', $joins[0], $groupByData);
            if (count($groupByData) > 0) {
                return $groupByData[1];
            }
        }

        throw new DpqlException('Cannot resolve grouping table alias (nothing matches)');
    }

    /**
     * @param SqlSelect $sql
     *
     * @return null|string
     */
    public static function getGroupingTargetTable(SqlSelect $sql)
    {
        if (count($groupBy = $sql->getGroupBy()) >= 1) {
            preg_match('/`(.+)`\.`(.+)`/isU', $groupBy[0], $groupByData);
            if (count($groupByData) > 0) {
                $tableAlias = $groupByData[1];
                $joins      = $sql->getJoins();
                if (array_key_exists($tableAlias, $joins)) {
                    preg_match("/.+`(.+)` AS `$tableAlias`.+/isU", $joins[$tableAlias], $joinData);
                    if (count($joinData) > 0) {
                        $joinTable = $joinData[1];
                        if (strpos($joinTable, 'custom_data_') === 0) {
                            return str_replace('custom_data_', 'custom_def_', $joinTable);
                        }

                        return $joinTable;
                    }
                }
            }
        }

        return $sql->getTable();
    }
}

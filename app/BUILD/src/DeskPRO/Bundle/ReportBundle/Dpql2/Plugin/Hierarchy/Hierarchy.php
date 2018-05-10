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
        $isGrouping       = count($sql->getGroupBy()) >= 1;
        $isSimpleGrouping = count($sql->getGroupBy()) === 1;

        list($groupingTargetTable, $groupingTargetField) = self::getGroupingTargetTable($sql, true);

        // hierarchy if:
        // we actually have a grouping
        // the grouping table is a known hierarchical table
        // or we're selecting a group field that s a custom field
        //     -- that field needs to be an 'id' field (value,input values are not hierarchical)
        // or if we see a def table, then just assume hierarchical

        if ((($isGrouping && $forceHierarchy) || $isSimpleGrouping) && (
                in_array($groupingTargetTable, self::$hierarchicalTables)
                || (is_string($groupingTargetTable) && $groupingTargetField === 'id' && strpos($groupingTargetTable, 'custom_data_') === 0)
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
     * Gets the table the grouping clause is on. This is the name as it appears in the query (i.e. could be
     * an alias). getGroupingTargetTable() is the "real" table name (i.e. de-aliased).
     *
     * @param SqlSelect $sql
     * @param bool      $returnFieldName Return an array of [table, fieldName] instead of just table
     *
     * @throws DpqlException
     *
     * @return string|string[]|null
     */
    public static function getGroupingTargetTableReference(SqlSelect $sql, $returnFieldName = false)
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
                if ($returnFieldName) {
                    // [table, fieldName]
                    // eg [tickets_custom_data_x, id]
                    return [$groupByData[1], $groupByData[2]];
                }

                return $groupByData[1];
            }
        }

        throw new DpqlException('Cannot resolve grouping table alias (nothing matches)');
    }

    /**
     * Gets the table the grouping clause is on.
     *
     * @param SqlSelect $sql
     * @param bool      $returnFieldName Return an array of [table, fieldName] instead of just table
     *
     * @return string|string[]|null
     */
    public static function getGroupingTargetTable(SqlSelect $sql, $returnFieldName = false)
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
                        //if (strpos($joinTable, 'custom_data_') === 0) {
                        //    return str_replace('custom_data_', 'custom_def_', $joinTable);
                        //}

                        if ($returnFieldName) {
                            return [$joinTable, $groupByData[2]];
                        }

                        return $joinTable;
                    }
                }
            }
        }

        return $sql->getTable();
    }
}

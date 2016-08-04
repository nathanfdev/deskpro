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

namespace Application\DeskPRO\Dpql\Plugin\Hierarchy;

use Application\DeskPRO\Dpql\Exception;
use Application\DeskPRO\Dpql\SqlSelect;

/**
 * Class Hierarchy.
 */
class Hierarchy
{
    private static $hierarchicalTables = [
        'departments', 'organizations', 'products', 'custom_field_definition',
        'article_categories', 'download_categories', 'feedback_categories', 'news_categories',
        'text_snippet_categories', 'ticket_categories',
    ];

    /**
     * @param SqlSelect $sql
     *
     * @return bool
     */
    public static function isHierarchical(SqlSelect $sql)
    {
        $isSimpleGrouping = count($sql->getGroupBy()) === 1;
        if ($isSimpleGrouping && in_array(self::getGroupingTargetTable($sql), self::$hierarchicalTables)) {
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
     * @throws Exception
     *
     * @return string
     */
    public static function getGroupingTargetTableReference(SqlSelect $sql)
    {
        $table = self::getGroupingTargetTable($sql);
        if ($table === $sql->getTable()) {
            return $table;
        } else {
            preg_match('/`(.+)`\.`(.+)`/isU', $sql->getGroupBy()[0], $groupByData);
            if (count($groupByData) > 0) {
                return $groupByData[1];
            }
        }

        throw new Exception('Cannot resolve grouping table alias');
    }

    /**
     * @param SqlSelect $sql
     *
     * @return null|string
     */
    public static function getGroupingTargetTable(SqlSelect $sql)
    {
        if (count($groupBy = $sql->getGroupBy()) === 1) {
            preg_match('/`(.+)`\.`(.+)`/isU', $groupBy[0], $groupByData);
            if (count($groupByData) > 0) {
                $tableAlias = $groupByData[1];
                $joins      = $sql->getJoins();
                if (array_key_exists($tableAlias, $joins)) {
                    preg_match("/.+`(.+)` AS `$tableAlias`.+/isU", $joins[$tableAlias], $joinData);
                    if (count($joinData) > 0) {
                        return $joinData[1];
                    }
                } else {
                    return $sql->getTable();
                }
            }
        }

        return;
    }
}

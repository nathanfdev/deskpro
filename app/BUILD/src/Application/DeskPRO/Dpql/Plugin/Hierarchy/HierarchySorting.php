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

use Application\DeskPRO\App;
use Application\DeskPRO\Dpql\Exception;

/**
 * Class HierarchySorting.
 */
class HierarchySorting
{
    /**
     * Processing uses trees building algorithm with complexity depending on a tree depth, normally it won't have more
     * iterations than the max depth and it will stop after an iteration hasn't added new vertexes into existing trees
     * (this can happen when data is not a proper forest i.e. some parent_id points to the non-existing object).
     *
     * Using this const as an additional security.
     */
    const ITERATIONS_LIMIT = 1000;

    /**
     * Process hierarchy data.
     *
     * Given $results elements with hierarchy_id and hierarchy_parent_id fields, this function sorts the elements as
     * they should appear in the view and adds hierarchy_depth and hierarchy_root_title fields
     *
     * @param array    $results
     * @param string   $hierarchicalTargetTable
     * @param string   $hierarchicalTargetTableAlias
     * @param array    $selectedFields
     * @param int|null $countFieldNum
     * @param int      $recursionLevel
     *
     * @throws Exception
     *
     * @return array
     */
    public static function sort(
        array $results,
        $hierarchicalTargetTable,
        $hierarchicalTargetTableAlias,
        array $selectedFields,
        $countFieldNum,
        $recursionLevel = 0
    ) {
        if ($recursionLevel > 1) {
            throw new Exception('
                The 3rd $recursionLevel param cannot be bigger than 1 because the only allowed recursive call is
                when at the end of tree-sort we get elements with missing parents and we load the parents and finish
                tree building with a single recursive call.
            ');
        }

        // Init the $newResults with root entries
        $newResults = [];
        foreach ($results as $k => $v) {
            self::ensureFields($v);

            if (!$v['hierarchy_parent_id']) {
                !$countFieldNum or $v['hierarchy_count'] = (int) $v[$countFieldNum];
                $v['hierarchy_depth']                    = 0;
                $v['hierarchy_root_title']               = $v['hierarchy_title'];
                $newResults[]                            = $v;
                unset($results[$k]);
            }
        }

        // If no root elements, then simply init the additional fields and return results as is
        // (e.g. we can have no root elements if using the HIERARCHY_DESCENDS_FROM() DPQL function)
        if (empty($newResults)) {
            foreach ($results as &$result) {
                $result['hierarchy_depth']                    = 0;
                !$countFieldNum or $result['hierarchy_count'] = (int) $result[$countFieldNum];
            }

            return $results;
        }

        // Starting from roots, move $results elements into $newResults at the appropriate places
        $iterationNum         = 0;
        $lastIterationInserts = 1;
        $processedNodeIds     = [];
        while (!empty($results) && $lastIterationInserts && ($iterationNum < self::ITERATIONS_LIMIT)) {
            ++$iterationNum;
            $lastIterationInserts = 0;
            foreach ($newResults as $i => $node) {
                if (in_array($node['hierarchy_id'], $processedNodeIds)) {
                    continue;
                }

                foreach ($results as $j => $potentialChild) {
                    if ($potentialChild['hierarchy_parent_id'] === $node['hierarchy_id']) {
                        $potentialChild['hierarchy_depth']                    = $node['hierarchy_depth'] + 1;
                        $potentialChild['hierarchy_root_title']               = $node['hierarchy_root_title'];
                        !$countFieldNum or $potentialChild['hierarchy_count'] = (int) $potentialChild[$countFieldNum];
                        array_splice($newResults, $i + ++$lastIterationInserts, 0, [$potentialChild]);
                        unset($results[$j]);
                    }
                }
                $processedNodeIds[] = $node['hierarchy_id'];
                if ($lastIterationInserts) {
                    break;
                }
            }
        }

        if ($iterationNum >= self::ITERATIONS_LIMIT) {
            throw new Exception('Reached the iterations limit when sorting hierarchical data');
        }

        // If there are orphan non-root elements w/o a corresponding root, we load the missing parents to
        // use them in the view (e.g. load missing parent elements with zero COUNT)
        if (!empty($results)) {
            $missing = [];
            foreach ($results as $result) {
                if (!in_array($result['hierarchy_parent_id'], $missing)) {
                    $missing[] = $result['hierarchy_parent_id'];
                }
            }

            foreach ($selectedFields as $i => $field) {
                if (strpos($field, "`$hierarchicalTargetTableAlias`.") !== 0) {
                    $selectedFields[$i] = "'-'";
                }
            }
            $selectedFieldsSql = implode(', ', $selectedFields);
            $sql               = "
                SELECT
                    $selectedFieldsSql,
                    0 as 'hierarchy_depth'
                FROM `$hierarchicalTargetTable` $hierarchicalTargetTableAlias
                WHERE id IN (?)
            ";
            $stmt = App::getDbRead('reports')->executeQuery(
                $sql, [$missing], [\Doctrine\DBAL\Connection::PARAM_INT_ARRAY]);
            $missing = $stmt->fetchAll(\PDO::FETCH_BOTH);

            $results = array_merge($missing, $results);
            $results = self::sort(
                $results, $hierarchicalTargetTable, $hierarchicalTargetTableAlias, $selectedFields, $countFieldNum,
                1 + $recursionLevel
            );
            $newResults = array_merge($newResults, $results);
        }

        return $newResults;
    }

    /**
     * @param array $entry
     *
     * @throws Exception
     */
    private static function ensureFields(array $entry)
    {
        if (!array_key_exists('hierarchy_id', $entry) || !key_exists('hierarchy_parent_id', $entry)) {
            throw new Exception(sprintf(
                'HierarchySorting expects each entry to have hierarchy_id and hierarchy_parent_id: %s',
                print_r($entry, true)
            ));
        }
    }
}

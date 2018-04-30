<?php

namespace DeskPRO\Bundle\ReportBundle\Dpql2\Plugin\Hierarchy;

use DeskPRO\Bundle\ReportBundle\Reports\ResultMetadata;

/**
 * Class HierarchyDepth.
 */
class HierarchyDepth
{
    /**
     * @param int            $min
     * @param int            $max
     * @param array          $results
     * @param ResultMetadata $metadata
     *
     * @return array
     */
    public static function limitTo($min, $max, array $results, ResultMetadata $metadata)
    {
        // Collapse titles
        if ($min !== 0) {
            foreach ($results as $i => &$result) {
                if ($result['hierarchy_depth'] === $min) {
                    for ($j = $i - 1; $j >= 0; --$j) {

                        // Skip siblings and children of siblings
                        if ($results[$j]['hierarchy_depth'] >= $min) {
                            continue;
                        }

                        // Join titles of parent nodes
                        $result['hierarchy_title'] = $results[$j]['hierarchy_title'].' > '.$result['hierarchy_title'];

                        // Break when reached the root
                        if ($results[$j]['hierarchy_depth'] === 0) {
                            break;
                        }
                    }
                }
            }
            unset($result);
        }

        // Filter out all with depth out of [$min, $max] range
        $results = array_filter($results, function ($result) use ($min) {
            return $result['hierarchy_depth'] >= $min;
        });

        $groupColumns = $metadata->getGroupYColumns();
        $groupColumn  = $groupColumns[0];

        foreach ($results as &$result) {
            // try to merge children
            if ($result['hierarchy_depth'] > $max) {
                $result[$groupColumn['resultId'] - 1] = $result['hierarchy_root_title'];
            }
        }
        unset($result);

        $mergeResults = $results;

        // populate root nodes with actual group field values
        // if we have group columns like IF('', '', '') then we can't get such data for missing root nodes
        // and we need to iterate the children nodes to get the all possible group values

        // e. g. IF(UNIX_TIMESTAMP(tickets.date_resolved) <= tickets.custom_data[target_complete_date], 'On Time', 'Overdue') AS 'Status'

        // and we got data like:

        //      root node, '-'
        //         -- child 1, 'On Time'
        //         -- child 2, 'Overdue'

        // so we replace it to:

        //      root node, 'On Time'
        //      root node, 'Overdue'
        //         -- child 1, 'On Time'
        //         -- child 2, 'Overdue'
        $uniqueHashes = [];
        foreach ($results as $i => $result) {
            if ($result['hierarchy_depth'] > $max) {
                continue;
            }

            foreach ($mergeResults as $mergeResult) {
                if ($mergeResult['hierarchy_depth'] <= $max || $mergeResult['hierarchy_root_title'] !== $result['hierarchy_root_title']) {
                    continue;
                }

                $newResult = $result;

                foreach ($groupColumns as $groupColumn) {
                    $expected = $result[$groupColumn['groupResultId'] - 1];
                    $actual   = $mergeResult[$groupColumn['groupResultId'] - 1];

                    if ($expected === '-' && $actual !== $expected) {
                        // replace group column
                        $newResult[$groupColumn['groupResultId'] - 1] = $actual;
                    }

                    // replace select column
                    if (isset($groupColumn['resultId']) && $result[$groupColumn['resultId'] - 1] === '-') {
                        $newResult[$groupColumn['resultId'] - 1] = $mergeResult[$groupColumn['resultId'] - 1];
                    }
                }

                $hash = md5(serialize($newResult));
                if (!isset($uniqueHashes[$hash])) {
                    $results[]           = $newResult;
                    $uniqueHashes[$hash] = true;
                }
            }
        }

        foreach ($results as $i => $result) {
            if ($result['hierarchy_depth'] > $max) {
                continue;
            }

            foreach ($groupColumns as $groupColumn) {
                if ($result[$groupColumn['groupResultId'] - 1] === '-') {
                    unset($results[$i]);
                    break;
                }
            }
        }

        // merge countable select columns
        // sum countable fields from children nodes, e.g.

        //      root node, 'On Time', 0
        //      root node, 'Overdue', 0
        //         -- child 1, 'On Time', 10
        //         -- child 2, 'On Time', 15
        //         -- child 3, 'Overdue', 20

        // so we will get:

        //      root node, 'On Time', 25
        //      root node, 'Overdue', 20

        $groupColumns = $metadata->getGroupYColumns();
        array_shift($groupColumns); // unshift DPQL_HIERARCHY columns from compare

        foreach ($results as &$result) {
            if ($result['hierarchy_depth'] > $max) {
                continue;
            }

            foreach ($mergeResults as $i => $mergeResult) {
                if ($mergeResult['hierarchy_depth'] <= $max || $mergeResult['hierarchy_root_title'] !== $result['hierarchy_root_title']) {
                    continue;
                }

                $found = true;
                foreach ($groupColumns as $groupColumn) {
                    $expected = $result[$groupColumn['groupResultId'] - 1];
                    $actual   = $mergeResult[$groupColumn['groupResultId'] - 1];

                    if ($expected !== $actual) {
                        $found = false;
                    }
                }

                if ($found) {
                    foreach ($metadata->getSelectColumns() as $column) {
                        if (is_numeric($mergeResult[$column['resultId'] - 1])) {
                            $result[$column['resultId'] - 1] = (float) $result[$column['resultId'] - 1] + (float) $mergeResult[$column['resultId'] - 1];
                        } elseif ($result[$column['resultId'] - 1] === '-') {
                            // copies constant values e.g. tooltip_text_template
                            $result[$column['resultId'] - 1] = $mergeResult[$column['resultId'] - 1];
                        }
                    }
                }
            }
        }
        unset($result);

        // remove children nodes
        $results = array_filter($results, function ($result) use ($max) {
            return $result['hierarchy_depth'] <= $max;
        });

        // Reduce depth by $min so that it starts from 0
        foreach ($results as &$result) {
            $result['hierarchy_depth'] = max(0, $result['hierarchy_depth'] - $min);
        }
        unset($result);

        return array_values($results);
    }
}

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
        }

        // Filter out all with depth out of [$min, $max] range
        $results = array_filter($results, function ($result) use ($min) {
            return $result['hierarchy_depth'] >= $min;
        });

        $groupColumns = $metadata->getGroupYColumns();
        $groupColumn  = array_shift($groupColumns); // unset first hierarchy one

        foreach ($results as &$result) {
            // try to merge children
            if ($result['hierarchy_depth'] > $max) {
                $result[$groupColumn['resultId'] - 1] = $result['hierarchy_root_title'];
            }
        }

        $mergeResults = $results;
        foreach ($results as &$result) {
            if ($result['hierarchy_depth'] > $max) {
                continue;
            }

            foreach ($mergeResults as $i => &$mergeResult) {
                if ($mergeResult['hierarchy_depth'] <= $max || $mergeResult['hierarchy_root_title'] !== $result['hierarchy_root_title']) {
                    continue;
                }

                $found = true;
                foreach ($groupColumns as $groupColumn) {
                    if ($mergeResult[$groupColumn['groupResultId']] !== $result[$groupColumn['groupResultId']]) {
                        $found = false;
                    }
                }

                if ($found) {
                    foreach ($metadata->getSelectColumns() as $column) {
                        if (is_numeric($mergeResult[$column['resultId'] - 1])) {
                            $result[$column['resultId'] - 1] = (float) $result[$column['resultId'] - 1] + (float) $mergeResult[$column['resultId'] - 1];
                        }
                    }
                }
            }
        }

        $results = array_filter($results, function ($result) use ($max) {
            return $result['hierarchy_depth'] <= $max;
        });

        // Reduce depth by $min so that it starts from 0
        foreach ($results as &$result) {
            $result['hierarchy_depth'] = max(0, $result['hierarchy_depth'] - $min);
        }

        return $results;
    }
}

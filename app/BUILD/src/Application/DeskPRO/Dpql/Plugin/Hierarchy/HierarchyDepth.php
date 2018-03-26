<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\Dpql\Plugin\Hierarchy;

/**
 * Class HierarchyDepth.
 */
class HierarchyDepth
{
    /**
     * @param int   $min
     * @param int   $max
     * @param array $results
     *
     * @return array
     */
    public static function limitTo($min, $max, array $results)
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
        $results = array_filter($results, function ($result) use ($min, $max) {
            return ($result['hierarchy_depth'] >= $min) && ($result['hierarchy_depth'] <= $max);
        });

        // Reduce depth by $min so that it starts from 0
        foreach ($results as &$result) {
            $result['hierarchy_depth'] = max(0, $result['hierarchy_depth'] - $min);
        }

        return $results;
    }
}

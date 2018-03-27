<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\Dpql\Plugin\Hierarchy;

use Application\DeskPRO\Dpql\Exception;

/**
 * Class HierarchyRollup.
 */
class HierarchyRollup
{
    /**
     * Adds hierarchy_rollup_count to the passed results.
     *
     * @param array $results
     *
     * @return array
     */
    public static function init(array $results)
    {
        $len = count($results);
        for ($i = 0; $i < $len; ++$i) {
            self::ensureFields($results[$i]);
            $results[$i]['hierarchy_rollup_count'] = $results[$i]['hierarchy_count'];
            $depth                                 = $results[$i]['hierarchy_depth'];
            for ($j = $i + 1; ($j < $len) && ($results[$j]['hierarchy_depth'] > $depth); ++$j) {
                self::ensureFields($results[$j]);
                $results[$i]['hierarchy_rollup_count'] += $results[$j]['hierarchy_count'];
            }
        }

        return $results;
    }

    /**
     * @param array $entry
     *
     * @throws Exception
     */
    private static function ensureFields(array $entry)
    {
        if (!array_key_exists('hierarchy_count', $entry)) {
            throw new Exception(sprintf(
                'HierarchyHandler expects each entry to have hierarchy_count: %s', print_r($entry, true)));
        }
    }
}

<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2017, DeskPRO Ltd.
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

namespace DeskPRO\Bundle\AppBundle\Dpql2\Plugin\Hierarchy;

use DeskPRO\Bundle\AppBundle\Dpql2\Exception;

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

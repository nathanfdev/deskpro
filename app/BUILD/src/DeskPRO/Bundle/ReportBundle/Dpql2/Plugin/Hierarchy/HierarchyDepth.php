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

namespace DeskPRO\Bundle\ReportBundle\Dpql2\Plugin\Hierarchy;

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

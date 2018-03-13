<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2018, DeskPRO Ltd.
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

namespace DeskPRO\Bundle\ReportBundle\Reports\Renderer\Json;

use DeskPRO\Bundle\ReportBundle\Reports\Renderer\AbstractRenderer;

/**
 * Class AbstractJsonRenderer.
 */
abstract class AbstractJsonRenderer extends AbstractRenderer
{
    /**
     * {@inheritdoc}
     */
    public static function getContentType()
    {
        return 'application/json';
    }

    /**
     * {@inheritdoc}
     */
    public static function getExtension()
    {
        return 'json';
    }

    /**
     * Filters a graph value that looks like a number into an actual number.
     *
     * @param string $value
     *
     * @return string|number
     */
    protected function filterGraphValue($value)
    {
        if (preg_match('/^((\d+,)*\d+)(\.\d+)?%?$/', $value)) {
            return round(str_replace([',', '%'], '', $value) + 0, 1);
        } else {
            return $value;
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function implodeSplitOutput(array $output)
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function renderSplitOutputWithHeader($header, $body)
    {
        return [];
    }

    /**
     * @param array $rows
     *
     * @return array
     */
    protected function collectHierarchyParents(array &$rows)
    {
        $hierarchyParents = [];
        foreach ($rows as $row) {
            if ($row['hierarchy_parent_id']) {
                $parent = $this->findHierarchyParent($rows, $row['hierarchy_parent_id']);
                if ($parent) {
                    list($index, $parent) = $parent;
                    // collect all hierarchy parents, so we gonna stack results under them
                    $hierarchyParents[$parent['hierarchy_id']] = $parent;
                    unset($rows[$index]);
                }
            }
        }

        return $hierarchyParents;
    }

    /**
     * @param array $rows
     * @param int   $parentId
     *
     * @return array|bool
     */
    protected function findHierarchyParent($rows, $parentId)
    {
        foreach ($rows as $i => $row) {
            if ($row['hierarchy_id'] === $parentId) {
                return [$i, $row];
            }
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function mergeResults(array $results)
    {
        $mainResults = array_pop($results);

        $assocKeyedDataProvider = [];
        foreach ($mainResults['dataProvider'] as $dataProviderItem) {
            $assocKeyedDataProvider[$dataProviderItem['category']] = $dataProviderItem;
        }
        $mainResults['dataProvider'] = $assocKeyedDataProvider;

        foreach ($results as $resultIndex => $result) {
            foreach ($result['dataProvider'] as $dataProviderItem) {
                if (isset($mainResults['dataProvider'][$dataProviderItem['category']])) {
                    foreach ($dataProviderItem as $itemKey => $value) {
                        if (strpos($itemKey, 'value') !== false) {
                            $newKey                                                              = $resultIndex.'_'.$itemKey;
                            $mainResults['dataProvider'][$dataProviderItem['category']][$newKey] = $value;
                        }
                    }
                }
            }

            foreach ($result['graphs'] as &$graph) {
                $graph['valueField'] = $resultIndex.'_'.$graph['valueField'];
                $graph['id']         = $resultIndex.'_'.$graph['id'];
                $graph['clustered']  = false;
                // should be less than 0.8, idk why but > 0.8 won't work
                $graph['columnWidth'] = 0.8 - 0.1 * ($resultIndex + 1);
            }
            $mainResults['graphs'] = array_merge($mainResults['graphs'], $result['graphs']);
        }
        $mainResults['dataProvider'] = array_values($mainResults['dataProvider']);

        return $mainResults;
    }
}

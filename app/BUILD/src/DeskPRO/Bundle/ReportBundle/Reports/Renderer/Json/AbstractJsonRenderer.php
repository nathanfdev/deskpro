<?php

namespace DeskPRO\Bundle\ReportBundle\Reports\Renderer\Json;

use DeskPRO\Bundle\ReportBundle\Reports\Renderer\AbstractRenderer;
use DeskPRO\Bundle\ReportBundle\Reports\SplitResult;

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
     * @param array $row
     * @param array $hierarchyParents
     *
     * @return string
     */
    protected function getFullHierarchyTitle(array $row, array $hierarchyParents)
    {
        $parts    = [];
        $iterator = function (array $row) use ($hierarchyParents, &$iterator, &$parts) {
            if (isset($row['hierarchy_parent_id']) && isset($hierarchyParents[$row['hierarchy_parent_id']])) {
                $iterator($hierarchyParents[$row['hierarchy_parent_id']]);
            }

            $parts[] = $row['hierarchy_title'] ?: 'None';
        };

        $iterator($row);

        return implode(' / ', $parts);
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
            if ((int) $row['hierarchy_id'] === (int) $parentId) {
                return [$i, $row];
            }
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function mergeResults(array $results, $graphType, array $options)
    {
        $mainResults = array_shift($results);

        $assocKeyedDataProvider = [];
        foreach ($mainResults['dataProvider'] as $dataProviderItem) {
            $assocKeyedDataProvider[$dataProviderItem['category']] = $dataProviderItem;
        }
        $mainResults['dataProvider'] = $assocKeyedDataProvider;
        foreach ($mainResults['graphs'] as &$graph) {
            $graph['columnWidth'] = 0.5;

            if ($graphType !== 'line') {
                $graph['fillAlphas'] = 0.9;
            }
        }

        foreach ($results as $resultIndex => $result) {
            $actualResult = $result;
            if ($result instanceof SplitResult) {
                $actualResult = $result->getResults();
            }
            foreach ($actualResult['dataProvider'] as $dataProviderItem) {
                if (isset($mainResults['dataProvider'][$dataProviderItem['category']])) {
                    foreach ($dataProviderItem as $itemKey => $value) {
                        if (strpos($itemKey, 'value') !== false) {
                            $newKey                                                              = $resultIndex.'_'.$itemKey;
                            $mainResults['dataProvider'][$dataProviderItem['category']][$newKey] = $value;
                        }
                        if (strpos($itemKey, 'title') !== false) {
                            $newKey                                                              = $resultIndex.'_'.$itemKey;
                            $mainResults['dataProvider'][$dataProviderItem['category']][$newKey] = $value;
                        }
                    }
                }
            }

            foreach ($actualResult['graphs'] as &$graph) {
                $graph['valueField'] = $resultIndex.'_'.$graph['valueField'];
                $graph['id']         = $resultIndex.'_'.$graph['id'];
                $graph['clustered']  = false;
                if (isset($graph['balloonText'])) {
                    $graph['balloonText'] = str_replace(
                        ['[[title]]', '[[value]]'],
                        ["[[{$resultIndex}_title]]", "[[{$resultIndex}_value]]"],
                        $graph['balloonText']
                    );
                }
            }
            $mainResults['graphs'] = array_merge($mainResults['graphs'], $actualResult['graphs']);
        }
        $mainResults['graphs']       = array_reverse($mainResults['graphs']);
        $mainResults['dataProvider'] = array_values($mainResults['dataProvider']);

        return $mainResults;
    }
}

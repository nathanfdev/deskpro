<?php

namespace DeskPRO\Bundle\ReportBundle\Reports\Renderer\Json;

use DeskPRO\Bundle\ReportBundle\Reports\Renderer\TextValueRenderer;
use DeskPRO\Bundle\ReportBundle\Reports\ResultMetadata;

/**
 * Class JsonChartRenderer.
 */
abstract class AbstractJsonChartRenderer extends AbstractJsonRenderer
{
    /**
     * @var array
     */
    protected $options = [];

    /**
     * Constructor.
     *
     * @param TextValueRenderer $valueRenderer
     */
    public function __construct(TextValueRenderer $valueRenderer)
    {
        $this->valueRenderer = $valueRenderer;
    }

    /**
     * @return array
     */
    protected function getDefaultOutputArray()
    {
        return [
            'dataProvider' => [],
            'categoryAxis' => [
                'gridPosition' => 'start',
                'axisAlpha'    => 0,
                'gridAlpha'    => 0,
                'position'     => 'left',
            ],
            'valueAxes' => [
                [],
            ],
            'graphs'        => [],
            'type'          => 'serial',
            'theme'         => 'none',
            'height'        => '100%',
            'reflow'        => true,
            'autoMargins'   => true,
            'pullOutRadius' => 0,
            'legend'        => [
                'horizontalGap'    => 10,
                'maxColumns'       => 1,
                'position'         => 'right',
                'useGraphSettings' => true,
                'markerSize'       => 10,
            ],
            'categoryField' => 'category',
            'exportConfig'  => false,
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function doRender(array $rows, ResultMetadata $metadata, array $options = [])
    {
        if (!$rows) {
            return null;
        }

        $arrayOutput   = $this->getDefaultOutputArray();
        $selectColumns = $metadata->getSelectColumns();
        $groupYColumns = $metadata->getGroupYColumns();
        $groupXColumns = $metadata->getGroupXColumns();

        $chartData          = [];
        $graphs             = [];
        $isStacked          = false;
        $maxCategoryLength  = 0;
        $integersOnly       = true;
        $firstSel           = reset($selectColumns);
        $valueLabelTemplate = $categoryLabelTemplate = $customBalloonText = $balloonTextTemplate = null;
        $valueAxisTitle     = $firstSel['title'];

        $additionalData = [];
        foreach ($selectColumns as $index => $selectColumn) {
            if (strpos($selectColumn['title'], '__var') !== false) {
                $additionalData[$selectColumn['title']] = $selectColumn;
                //we're gonna add this column in another way, it should have same key for it and would be used for
                // click_url option in chart
                unset($selectColumns[$index]);
            }
            // it would be same for each row of course since it
            // SELECT blah-blah-blah
            // '{{value * 4}} as 'value_label_template'
            if ($selectColumn['title'] === 'value_label_template') {
                $valueLabelTemplate = $rows[0][$selectColumn['resultId'] - 1];
                unset($selectColumns[$index]);
            }
            if ($selectColumn['title'] === 'category_label_template') {
                $categoryLabelTemplate = $rows[0][$selectColumn['resultId'] - 1];
                unset($selectColumns[$index]);
            }
            if ($selectColumn['title'] === 'tooltip_text') {
                $customBalloonText = $rows[0][$selectColumn['resultId'] - 1];
                unset($selectColumns[$index]);
            }
            if ($selectColumn['title'] === 'tooltip_text_template') {
                $balloonTextTemplate = $rows[0][$selectColumn['resultId'] - 1];
                unset($selectColumns[$index]);
            }
            if ($selectColumn['title'] === 'value_axis_title') {
                $valueAxisTitle = $rows[0][$selectColumn['resultId'] - 1];
                unset($selectColumns[$index]);
            }
        }

        if ($groupXColumns && !$metadata->hasFlag(ResultMetadata::FLAG_HIERARCHICAL)) {
            // matrix table - X() values translate to bottom axis, each row (from Y()) is a new line/stack.
            $prepared = $this->prepareMatrixTable($metadata, $rows);
            $lookup   = $prepared['lookup'];

            $rowGroups = $this->getFinalMatrixPathsWithPrintable(['root'], $prepared['yDistinct']);
            if (!$rowGroups) {
                // need to fake it so we get a row with no Y grouping
                $rowGroups = ['root' => []];
            }
            $headerCols = $this->getFinalMatrixPathsWithPrintable(['root'], $prepared['xDistinct']);

            foreach ($headerCols as $xPath => $printable) {
                $category          = implode(' / ', $printable);
                $maxCategoryLength = max($maxCategoryLength, strlen($category));

                $rowData = ['category' => $category];

                $i = 0;
                foreach ($rowGroups as $yPath => $null) {
                    if (isset($lookup[$yPath][$xPath])) {
                        $value = $this->filterGraphValue($lookup[$yPath][$xPath]);
                    } else {
                        $value = '';
                    }

                    /*
                     * @see https://deskpro.myjetbrains.com/youtrack/issue/DP-1503
                     */
                    if (!$integersOnly || !filter_var($value, FILTER_VALIDATE_INT)) {
                        $integersOnly = false;
                    }

                    $rowData['value'.$i] = $value;
                    ++$i;
                }
                $chartData[] = $rowData;
            }

            $i = 0;
            foreach ($rowGroups as $printable) {
                $graphs[$i] = [
                    'id'    => 'graph-'.$i,
                    'title' => implode(' / ', $printable),
                    'value' => "value$i",
                ];
                ++$i;
            }

            $resultingArray = array_udiff($groupXColumns, $groupYColumns,
                function ($a, $b) {
                    if ($a['title'] === $b['title'] && $a['resultId'] === $b['resultId']) {
                        return 0;
                    }

                    return 1;
                }
            );
            $hasCategory = count($resultingArray) > 0 && count($groupYColumns) > 0 && count($groupXColumns) > 0;
            $isStacked   = (static::getOutputFormat() == 'bar' || static::getOutputFormat() == 'area');

            $parts = [];
            foreach ($groupXColumns as $column) {
                $parts[] = $column['title'];
            }
            $categoryAxisTitle = implode(' / ', $parts);
        } else {
            if (count($groupYColumns) > 1) {
                $rowGroups = [];
                foreach ($rows as $row) {
                    $categories = [];
                    $grouper    = '';
                    $i          = 0;
                    foreach ($groupYColumns as $column) {
                        ++$i;
                        if ($i == 1) {
                            $grouper = $this->renderCellValue($row, $column, $metadata);
                            continue;
                        } else {
                            $categories[] = $this->renderCellValue($row, $column, $metadata);
                        }
                    }
                    $category = implode(' / ', $categories);

                    $rowData = [];

                    foreach ($selectColumns as $i => $column) {
                        $value = $this->filterGraphValue($this->getColumnValue($row, $column));

                        /*
                         * @see https://deskpro.myjetbrains.com/youtrack/issue/DP-1503
                         */
                        if (!$integersOnly || !filter_var($value, FILTER_VALIDATE_INT)) {
                            $integersOnly = false;
                        }

                        $rowData['value'.$i] = $value;
                    }

                    // process additional data for internal chart purposes
                    foreach ($additionalData as $key => $column) {
                        $value = $this->filterGraphValue($this->getColumnValue($row, $column));
                        /*
                         * @see https://deskpro.myjetbrains.com/youtrack/issue/DP-1503
                         */
                        if (!$integersOnly || !filter_var($value, FILTER_VALIDATE_INT)) {
                            $integersOnly = false;
                        }
                        $rowData[$key] = $value;
                    }

                    $rowGroups[$grouper][$category] = $rowData;
                }

                $uniqueGraphs = [];

                foreach ($rowGroups as $grouper => $values) {
                    $maxCategoryLength = max($maxCategoryLength, strlen($grouper));

                    $data = ['category' => $grouper];
                    foreach ($values as $categoryName => $groupValues) {
                        $uniqueGraphs[$categoryName] = true;
                        foreach ($groupValues as $valueId => $value) {
                            $key = "$categoryName-$valueId";
                            // this value is used for internal purposes, don't change key name
                            if (strpos($valueId, '__var') !== false) {
                                $key = $valueId;
                            }
                            $data[$key] = $value;
                        }
                    }

                    $chartData[] = $data;
                }

                foreach ($uniqueGraphs as $categoryName => $null) {
                    $graphs[] = [
                        'id'    => "graph-$categoryName",
                        'title' => "$categoryName",
                        'value' => "$categoryName-value0",
                    ];
                }

                $isStacked = (static::getOutputFormat() == 'bar' || static::getOutputFormat() == 'area');

                $firstY            = reset($groupYColumns);
                $categoryAxisTitle = $firstY['title'];
            } elseif ($metadata->getGroupStackColumns() && static::getOutputFormat() == 'bar') {
                $stackColumns = $metadata->getGroupStackColumns();

                $rowGroups = [];
                foreach ($rows as $row) {
                    $categories = [];
                    $grouper    = $this->valueRenderer->renderValue($this->getColumnValue($row, $stackColumns[0]['printId']), 'string', $metadata);
                    foreach ($groupYColumns as $column) {
                        $categories[] = $this->renderCellValue($row, $column, $metadata);
                    }
                    $category = implode(' / ', $categories);

                    $rowData = [];

                    foreach ($selectColumns as $i => $column) {
                        $value = $this->filterGraphValue($this->getColumnValue($row, $column));
                        /*
                         * @see https://deskpro.myjetbrains.com/youtrack/issue/DP-1503
                         */
                        if (!$integersOnly || !filter_var($value, FILTER_VALIDATE_INT)) {
                            $integersOnly = false;
                        }
                        $rowData['value'.$i] = $value;
                    }

                    // process additional data for internal chart purposes
                    foreach ($additionalData as $key => $column) {
                        $value = $this->filterGraphValue($this->getColumnValue($row, $column));
                        /*
                         * @see https://deskpro.myjetbrains.com/youtrack/issue/DP-1503
                         */
                        if (!$integersOnly || !filter_var($value, FILTER_VALIDATE_INT)) {
                            $integersOnly = false;
                        }
                        $rowData[$key] = $value;
                    }

                    $rowGroups[$grouper][$category] = $rowData;
                }

                $uniqueGraphs = [];

                foreach ($rowGroups as $grouper => $values) {
                    $maxCategoryLength = max($maxCategoryLength, strlen($grouper));

                    $data = ['category' => $grouper];
                    foreach ($values as $categoryName => $groupValues) {
                        $uniqueGraphs[$categoryName] = true;
                        foreach ($groupValues as $valueId => $value) {
                            $key = "$categoryName-$valueId";

                            // this value is used for internal purposes, don't change key name
                            if (strpos($valueId, '__var') !== false) {
                                $key = $valueId;
                            }
                            $data[$key] = $value;
                        }
                    }

                    $chartData[] = $data;
                }

                foreach ($uniqueGraphs as $categoryName => $null) {
                    $graphs[] = [
                        'id'    => "graph-$categoryName",
                        'title' => "$categoryName",
                        'value' => "$categoryName-value0",
                    ];
                }

                $isStacked = (static::getOutputFormat() == 'bar' || static::getOutputFormat() == 'area');

                $firstY            = reset($groupYColumns);
                $categoryAxisTitle = $firstY['title'];
            } else {
                $sel = reset($selectColumns);

                if ($metadata->hasFlag(ResultMetadata::FLAG_LAYERED) && $metadata->hasFlag(ResultMetadata::FLAG_HIERARCHICAL)) {
                    $this->collectHierarchyParents($rows); // this gonna remove hierarchy from results
                }

                foreach ($rows as $row) {
                    $categories = [];
                    foreach ($groupYColumns as $column) {
                        $categories[] = $this->renderCellValue($row, $column, $metadata);
                    }
                    if ($metadata->hasFlag(ResultMetadata::FLAG_HIERARCHICAL) && $row['hierarchy_parent_id'] && isset($row['hierarchy_root_title'])) {
                        array_unshift($categories, $row['hierarchy_root_title']);
                    }

                    $category          = implode(' / ', $categories);
                    $maxCategoryLength = max($maxCategoryLength, strlen($category));

                    $value = $this->filterGraphValue($this->getColumnValue($row, $sel));
                    /*
                     * @see https://deskpro.myjetbrains.com/youtrack/issue/DP-1503
                     */
                    if (!$integersOnly || !filter_var($value, FILTER_VALIDATE_INT)) {
                        $integersOnly = false;
                    }
                    $data = [
                        'category' => $category,
                        'title'    => $sel['title'],
                        'value'    => $value,
                    ];

                    // process additional data for internal chart purposes
                    foreach ($additionalData as $key => $column) {
                        $value = $this->filterGraphValue($this->getColumnValue($row, $sel));
                        /*
                         * @see https://deskpro.myjetbrains.com/youtrack/issue/DP-1503
                         */
                        if (!$integersOnly || !filter_var($value, FILTER_VALIDATE_INT)) {
                            $integersOnly = false;
                        }
                        $data[$key] = $value;
                    }

                    $chartData[] = $data;
                }

                $graphs[] = [
                    'id'    => 'graph-'.$sel['title'],
                    'title' => $sel['title'],
                    'value' => 'value',
                ];

                $parts = [];
                foreach ($groupYColumns as $column) {
                    $parts[] = $column['title'];
                }
                $categoryAxisTitle = implode(' / ', $parts);
            }

            $hasCategory = count($groupYColumns) > 0;
        }

        if (static::getOutputFormat() === 'pie') {
            $pieData = [];

            if (count($graphs) > 1) {
                foreach ($chartData as $key => $info) {
                    $data = [];
                    foreach ($graphs as $graph) {
                        $dataToPush = [];
                        if (isset($info[$graph['value']])) {
                            $dataToPush['category'] = $graph['title'];
                            $dataToPush['value']    = $info[$graph['value']];
                        }

                        if (!empty($dataToPush)) {
                            // additional data only for those data which contain something already
                            foreach ($additionalData as $key => $additionalDatum) {
                                if (isset($info[$key])) {
                                    $dataToPush[$key] = $info[$key];
                                }
                            }
                        }

                        // push data only in case we have something
                        if (!empty($dataToPush)) {
                            $data[] = $dataToPush;
                        }
                    }

                    $pieData[] = [
                        'title' => $info['category'],
                        'data'  => $data,
                    ];
                }

                // let's add a graph for the first level of grouping
                $data = [];
                foreach ($pieData as $k => $pie) {
                    $sum = 0;
                    foreach ($pie['data'] as $info) {
                        $sum += (int) $info['value'];
                    }

                    $dataToPush = [
                        'category' => $pie['title'],
                        'value'    => $sum,
                        'id'       => $k,
                        'color'    => $this->randomColor(),
                    ];

                    foreach ($additionalData as $key => $additionalDatum) {
                        if (isset($pie['data'][0]) && isset($pie['data'][0]) && isset($pie['data'][0][$key])) {
                            $dataToPush[$key] = $pie['data'][0][$key];
                        }
                    }

                    $data[] = $dataToPush;
                }

                array_unshift($pieData, [
                    'title' => 'Overall',
                    'data'  => $data,
                ]);
            } else {
                $graph = reset($graphs);

                $pieData = [
                    [
                        'title' => $graph['title'],
                        'data'  => $chartData,
                    ],
                ];
            }

            $arrayOutput['type']             = 'pie';
            $arrayOutput['startDuration']    = 0;
            $arrayOutput['titleField']       = 'category';
            $arrayOutput['valueField']       = 'value';
            $arrayOutput['legend']           = false;
            $arrayOutput['outlineColor']     = '#ffffff';
            $arrayOutput['outlineAlpha']     = '0.8';
            $arrayOutput['outlineThickness'] = '2';
            $arrayOutput['colorField']       = 'color';
            $arrayOutput['pulledField']      = 'pulled';
            if (count($pieData) > 1) {
                $overAllPie                  = array_shift($pieData);
                $arrayOutput['dataProvider'] = $overAllPie['data'];
//                $arrayOutput['legend']['title'] = $overAllPie['title'];
                $arrayOutput['multiplePies'] = true;
                if (count($overAllPie['data']) > 25) {
                    $arrayOutput['labelsEnabled'] = false;
                }
                $arrayOutput['pies'] = [];
                foreach ($pieData as $k => $pie) {
                    $arrayOutput['pies'][] = [
                        'dataProvider'  => $pie['data'],
                        'legend'        => ['title' => $pie['title']],
                        'labelsEnabled' => count($pie['data']) > 25 ? false : true,
                    ];
                }
            } else {
                foreach ($pieData as $pie) {
                    $arrayOutput['dataProvider']    = $pie['data'];
                    $arrayOutput['legend']['title'] = $pie['title'];
                    if (count($pie['data']) > 25) {
                        $arrayOutput['labelsEnabled'] = false;
                    }
                }
            }
        } else {
            if ($hasCategory) {
                $balloonText = '[[category]], [[title]]: [[value]]';
            } else {
                $balloonText = '[[title]]: [[value]]';
            }
            $graphArray = [];
            foreach ($graphs as $graph) {
                $graphArray[] = array_merge($this->options, [
                    'valueField'          => $graph['value'],
                    'id'                  => $graph['id'],
                    'title'               => $graph['title'],
                    'balloonText'         => $customBalloonText ?: $balloonText,
                    'balloonTextTemplate' => $balloonTextTemplate,
                ]);
            }

            if ($isStacked) {
                $arrayOutput['valueAxes'][0]['stackType'] = 'regular';
            }
            if ($maxCategoryLength > 10) {
                $labelHeight                 = $maxCategoryLength * 4;
                $arrayOutput['categoryAxis'] = array_merge($arrayOutput['categoryAxis'], [
                    'labelRotation' => 45,
                    'gridCount'     => min(15, count($rows)),
                    'marginBottom'  => $labelHeight,
                ]);
            }
            if ($isStacked) {
                $chartData = $this->fillInGraphValues($chartData);
            }

            if (isset($selectColumns[0]) && isset($selectColumns[0]['renderer']) && $selectColumns[0]['renderer'] == 'percent') {
                $arrayOutput['valueAxes'][0]['unit'] = '%';
                $arrayOutput['valueAxis']            = [
                    'maximum' => 100,
                    'minimum' => 0,
                ];
            }

            $arrayOutput['dataProvider']                 = $chartData;
            $arrayOutput['categoryAxis']['title']        = $categoryAxisTitle;
            $arrayOutput['valueAxes'][0]['title']        = $valueAxisTitle;
            $arrayOutput['valueAxes'][0]['integersOnly'] = $integersOnly;
            $arrayOutput['graphs']                       = $graphArray;
        }

        if ($arrayOutput['valueAxes'][0]) {
            $arrayOutput['valueAxes'][0]['labelTemplate'] = $valueLabelTemplate;
        }
        $arrayOutput['categoryAxis']['labelTemplate'] = $categoryLabelTemplate;

        return $arrayOutput;
    }

    /**
     * @param $chartData
     *
     * @return mixed
     */
    protected function fillInGraphValues($chartData)
    {
        $uniqueValues = [];
        foreach ($chartData as $values) {
            foreach ($values as $value => $null) {
                if (!isset($uniqueValues[$value])) {
                    $uniqueValues[$value] = true;
                }
            }
        }
        foreach ($chartData as &$values) {
            foreach ($uniqueValues as $value => $null) {
                if (!isset($values[$value])) {
                    $values[$value] = 0;
                }
            }
        }

        return $chartData;
    }

    /**
     * @return string
     */
    protected function randomColor()
    {
        return '#'
            .str_pad(dechex(mt_rand(0, 255)), 2, '0', STR_PAD_LEFT)
            .str_pad(dechex(mt_rand(0, 255)), 2, '0', STR_PAD_LEFT)
            .str_pad(dechex(mt_rand(0, 255)), 2, '0', STR_PAD_LEFT);
    }
}

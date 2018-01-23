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
     * {@inheritdoc}
     */
    protected function doRender(array $rows, ResultMetadata $metadata, array $options = [])
    {
        if (!$rows) {
            return '';
        }

        //initial output array
        $arrayOutput = [
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
            'exportConfig'  => [
                'menuTop'   => '20px',
                'menuRight' => '20px',
                'menuItems' => [
                    [
                        'icon'   => '/lib/3/images/export.png',
                        'format' => 'png',
                    ],
                ],
            ],
        ];

        $selectColumns = $metadata->getSelectColumns();
        $groupYColumns = $metadata->getGroupYColumns();
        $groupXColumns = $metadata->getGroupXColumns();

        $chartData         = [];
        $graphs            = [];
        $isStacked         = false;
        $maxCategoryLength = 0;

        $firstSel       = reset($selectColumns);
        $valueAxisTitle = $firstSel['title'];

        if ($groupXColumns) {
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
                    $rowData['value'.$i] = $value;
                    ++$i;
                }
                $chartData[] = $rowData;
            }

            $i = 0;
            foreach ($rowGroups as $printable) {
                $graphs[$i] = [
                    'title' => implode(' / ', $printable),
                    'value' => "value$i",
                ];
                ++$i;
            }

            $hasCategory = true;
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
                            $grouper = $this->renderCellValue($row, $column);
                            continue;
                        } else {
                            $categories[] = $this->renderCellValue($row, $column);
                        }
                    }
                    $category = implode(' / ', $categories);

                    $rowData = [];

                    foreach ($selectColumns as $i => $column) {
                        $rowData['value'.$i] = $this->filterGraphValue($this->getColumnValue($row, $column));
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
                            $data["$categoryName-$valueId"] = $value;
                        }
                    }

                    $chartData[] = $data;
                }

                foreach ($uniqueGraphs as $categoryName => $null) {
                    $graphs[] = [
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
                    $grouper    = $this->valueRenderer->renderValue($this->getColumnValue($row, $stackColumns[0]['printId']), 'string');
                    foreach ($groupYColumns as $column) {
                        $categories[] = $this->renderCellValue($row, $column);
                    }
                    $category = implode(' / ', $categories);

                    $rowData = [];

                    foreach ($selectColumns as $i => $column) {
                        $rowData['value'.$i] = $this->filterGraphValue($this->getColumnValue($row, $column));
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
                            $data["$categoryName-$valueId"] = $value;
                        }
                    }

                    $chartData[] = $data;
                }

                foreach ($uniqueGraphs as $categoryName => $null) {
                    $graphs[] = [
                        'title' => "$categoryName",
                        'value' => "$categoryName-value0",
                    ];
                }

                $isStacked = (static::getOutputFormat() == 'bar' || static::getOutputFormat() == 'area');

                $firstY            = reset($groupYColumns);
                $categoryAxisTitle = $firstY['title'];
            } else {
                $sel = reset($selectColumns);

                foreach ($rows as $row) {
                    $categories = [];
                    foreach ($groupYColumns as $column) {
                        $categories[] = $this->renderCellValue($row, $column);
                    }
                    $category = implode(' / ', $categories);

                    $maxCategoryLength = max($maxCategoryLength, strlen($category));

                    $rowData = ['category' => $category];

                    $rowData['value'] = $this->filterGraphValue($this->getColumnValue($row, $sel));

                    $chartData[] = $rowData;
                }

                $graphs[] = [
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

        if (static::getOutputFormat() == 'pie') {
            $pieData = [];

            if (count($graphs) > 1) {
                foreach ($chartData as $key => $info) {
                    $data = [];
                    foreach ($graphs as $graph) {
                        if (isset($info[$graph['value']])) {
                            $data[] = [
                                'category' => $graph['title'],
                                'value'    => $info[$graph['value']],
                                'pulled'   => true,
                            ];
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
                    $data[] = [
                        'category' => $pie['title'],
                        'value'    => $sum,
                        'id'       => $k,
                        'color'    => $this->randomColor(),
                    ];
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
                    'valueField'  => $graph['value'],
                    'title'       => $graph['title'],
                    'balloonText' => $balloonText,
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
            $arrayOutput['dataProvider']          = $chartData;
            $arrayOutput['categoryAxis']['title'] = $categoryAxisTitle;
            $arrayOutput['valueAxes'][0]['title'] = $valueAxisTitle;
            $arrayOutput['graphs']                = $graphArray;
        }

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

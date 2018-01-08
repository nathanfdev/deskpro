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

namespace DeskPRO\Bundle\ReportBundle\Reports\Renderer\Html;

use DeskPRO\Bundle\ReportBundle\Reports\Renderer\TextValueRenderer;
use DeskPRO\Bundle\ReportBundle\Reports\ResultMetadata;

/**
 * Class HtmlChartsRenderer.
 */
abstract class AbstractHtmlChartsRenderer extends AbstractHtmlRenderer
{
    /**
     * @var string
     */
    protected $options = '';

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
                $category = $metadata->hasFlag(ResultMetadata::FLAG_HIERARCHICAL)
                    ? str_replace('root|', '', $xPath)
                    : implode(' / ', $printable);
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
            $output = '';

            $sliceCount = count($chartData);
            if ($maxCategoryLength > 25) {
                $divisor = 1;
            } elseif ($maxCategoryLength > 15) {
                $divisor = 2;
            } else {
                $divisor = 4;
            }
            $height = 400 + ceil($sliceCount / $divisor) * 30;

            $pieData = [];

            if (count($graphs) > 1) {
                foreach ($chartData as $key => $info) {
                    $data = [];
                    foreach ($graphs as $graph) {
                        if (isset($info[$graph['value']])) {
                            $data[] = [
                                'category' => $graph['title'],
                                'value'    => $info[$graph['value']],
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
                foreach ($pieData as $pie) {
                    $sum = 0;
                    foreach ($pie['data'] as $info) {
                        $sum += (int) $info['value'];
                    }
                    $data[] = [
                        'category' => $pie['title'],
                        'value'    => $sum,
                    ];
                }

                array_unshift($pieData, [
                    'title' => 'Overall',
                    'data'  => $data,
                ]);
            } else {
                $graph = reset($graphs);

                $pieData = [[
                    'title' => $graph['title'],
                    'data'  => $chartData,
                ]];
            }

            $showTitle = count($pieData) > 1;

            foreach ($pieData as $pie) {
                $id = 'report_chart_'.md5(uniqid());

                $output .= '
                    <div id="'.$id.'" class="report-chart" style="height: '.$height.'px"></div>
                    <script type="text/javascript">
                    $(function () {
                        var chart = new AmCharts.AmPieChart();
                        chart.dataProvider = '.json_encode($pie['data']).';
                        chart.titleField = "category";
                        chart.valueField = "value";
                        chart.startDuration = 0;
                        '.(count($pie['data']) >= 25 ? 'chart.labelsEnabled = false;' : '').'
                        chart.addLegend(new AmCharts.AmLegend());
                        '.($showTitle ? 'chart.addTitle('.json_encode($pie['title']).');' : '').'

                        chart.write("'.$id.'");
                    });
                    </script>
                ';
            }
        } else {
            if ($hasCategory) {
                $balloonText = '[[category]], [[title]]: [[value]]';
            } else {
                $balloonText = '[[title]]: [[value]]';
            }

            $graphCode = [];
            foreach ($graphs as $graph) {
                $graphCode[] = '
                    graph = new AmCharts.AmGraph();
                    graph.valueField = "'.$graph['value'].'";
                    graph.title = "'.$this->jsEscapeValue($graph['title']).'";
                    graph.balloonText = "'.$balloonText.'";
                    '.$this->options.'
                    chart.addGraph(graph);
                ';
            }

            if ($isStacked) {
                $stacked = 'chart.valueAxes[0].stackType = "regular";';
            } else {
                $stacked = '';
            }

            $height = 430 + count($graphs) * 25;

            if ($maxCategoryLength > 10) {
                $labelHeight    = $maxCategoryLength * 4;
                $verticalLabels = '
                    chart.categoryAxis.labelRotation = 45;
                    chart.categoryAxis.gridCount = '.min(15, count($rows)).';
                    chart.marginBottom = '.$labelHeight.';
                ';
                $height += (int) $labelHeight;
            } else {
                $verticalLabels = '';
            }

            if ($isStacked) {
                $chartData = $this->fillInGraphValues($chartData);
            }

            $percent_code = '';
            if (isset($selectColumns[0]) && isset($selectColumns[0]['renderer']) && $selectColumns[0]['renderer'] == 'percent') {
                $percent_code = '
                    valueAxis.maximum = 100;
                    valueAxis.minimum = 0;
                ';
            }

            $id     = 'report_chart_'.md5(uniqid());
            $output = '
                <div id="'.$id.'" class="report-chart" style="height: '.$height.'px"></div>
                <script type="text/javascript">
                $(function () {
                    var chart = new AmCharts.AmSerialChart();
                    chart.dataProvider = '.json_encode($chartData).';
                    chart.categoryField = "category";
                    chart.addLegend(new AmCharts.AmLegend());

                    chart.categoryAxis.fontSize = 9;
                    chart.categoryAxis.title = \''.$this->jsEscapeValue($categoryAxisTitle).'\';
                    '.$verticalLabels.'

                    var valueAxis = new AmCharts.ValueAxis();
                    '.$percent_code.'

                    chart.addValueAxis(valueAxis);
                    chart.valueAxes[0].integersOnly = true;
                    chart.valueAxes[0].title = \''.$this->jsEscapeValue($valueAxisTitle).'\';
                    '.$stacked.'

                    var graph;
                    '.implode("\n", $graphCode).'

                    chart.write("'.$id.'");
                    chart.invalidateSize();
                });
                </script>
            ';
        }

        return $output;
    }

    /**
     * @param string $value
     *
     * @return string
     */
    protected function jsEscapeValue($value)
    {
        return strtr($value, [
            '"'         => '\\"',
            "'"         => "\\'",
            '\\'        => '\\\\',
            '</script>' => '<\\/script>',
        ]);
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
}

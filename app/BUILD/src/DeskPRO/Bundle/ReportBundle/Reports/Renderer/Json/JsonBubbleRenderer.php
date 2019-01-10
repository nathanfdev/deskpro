<?php

namespace DeskPRO\Bundle\ReportBundle\Reports\Renderer\Json;

use DeskPRO\Bundle\ReportBundle\Reports\ResultMetadata;
use Symfony\Bundle\FrameworkBundle\Templating\Helper\AssetsHelper;

/**
 * Class JsonBubbleRenderer.
 */
class JsonBubbleRenderer extends AbstractJsonChartRenderer
{
    /**
     * Constructor.
     *
     * @param JsonValueRenderer $valueRenderer
     * @param AssetsHelper      $assetsHelper
     */
    public function __construct(JsonValueRenderer $valueRenderer, AssetsHelper $assetsHelper)
    {
        $this->valueRenderer = $valueRenderer;
        $this->assetsHelper  = $assetsHelper;
    }

    /**
     * {@inheritdoc}
     */
    public static function getOutputFormat()
    {
        return self::TYPE_BUBBLE;
    }

    public function doRender(array $rows, ResultMetadata $metadata, array $options = [])
    {
        if (!$rows) {
            return null;
        }

        //initial output array
        $arrayOutput = [
            'dataProvider' => [],
            'valueAxes'    => [
                [
                    'id'            => 'valueAxis-1',
                    'axisFrequency' => 1,
                ],
                [
                    'id'            => 'valueAxis-2',
                    'axisFrequency' => 1,
                ],
            ],
            'graphs'        => [],
            'type'          => 'xy',
            'theme'         => 'none',
            'height'        => '100%',
            'reflow'        => true,
            'autoMargins'   => true,
            'pullOutRadius' => 0,
            'legend'        => false,
            'export'        => [
                'enabled' => true,
                'libs'    => [
                    'path' => $this->assetsHelper->getUrl('vendor/amcharts3/libs/', 'legacy_web'),
                ],
                'menu' => [],
            ],
        ];

        $selectColumns = $metadata->getSelectColumns();

        $groupYColumnTitleId = $groupXColumnTitleId = $groupYColumnId = $groupXColumnId = 0;
        $columnYConfig       = $columnXConfig       = [];

        // this gonna be y-axis
        $groupYColumns = $metadata->getGroupYColumns();

        if ($groupYColumns && $groupYColumns[0]) {
            $columnYConfig       = $groupYColumns[0];
            $groupYColumnId      = $columnYConfig['groupResultId'] ? $columnYConfig['groupResultId'] - 1 : $columnYConfig['resultId'];
            $groupYColumnTitleId = $columnYConfig['resultId'] - 1;
            $yAxis['title']      = $columnYConfig['title'];
            // collection unique values
            $hash = [];
            foreach ($rows as $row) {
                $hash[$row[$groupYColumnId]] = $row[$groupYColumnTitleId];
            }
            $yAxis['hash']               = $hash;
            $arrayOutput['valueAxes'][0] = array_merge($arrayOutput['valueAxes'][0], $yAxis);
        }

        // this gonna be x-axis values
        $groupXColumns = $metadata->getGroupXColumns();
        if ($groupXColumns && $groupXColumns[0]) {
            $columnXConfig       = $groupXColumns[0];
            $groupXColumnId      = is_int($columnXConfig['groupResultId']) ? $columnXConfig['groupResultId'] - 1 : $columnXConfig['resultId'];
            $groupXColumnTitleId = $columnXConfig['resultId'] - 1;
            $xAxis['title']      = $columnXConfig['title'];
            // collection unique values
            $hash = [];
            $min  = $max  = 0;
            foreach ($rows as $row) {
                $hash[$row[$groupXColumnId]] = $row[$groupXColumnTitleId];
                $min                         = min($min, $row[$groupXColumnId]);
                $max                         = max($max, $row[$groupXColumnId]);
            }
            $xAxis['hash']               = $hash;
            $xAxis['minimum']            = $min;
            $xAxis['maximum']            = $max;
            $arrayOutput['valueAxes'][1] = array_merge($arrayOutput['valueAxes'][1], $xAxis);
        }

        $chartData = []; // actual data
        foreach ($rows as $row) {
            $dataPiece = [];
            $values    = [];
            foreach ($selectColumns as $i => $column) {
                $values['value'.$i] = $this->getColumnValue($row, $column);
            }
            $dataPiece = array_merge($dataPiece, $values);

            if ($groupYColumnId) {
                $dataPiece['y'] = $row[$groupYColumnId];
            }
            if ($groupXColumnId) {
                $dataPiece['x'] = $row[$groupXColumnId];
            }

            if ($groupXColumnTitleId) {
                $dataPiece['xTitle'] = $row[$groupXColumnTitleId];
            }
            if ($groupYColumnTitleId) {
                $dataPiece['yTitle'] = $row[$groupYColumnTitleId];
            }
            $chartData[] = $dataPiece;
        }

        $balloonText = "<div style='margin:5px;'>X:<b>[[xTitle]]</b><br>Y:<b>[[yTitle]]</b><br>%s:<b>[[value]]</b></div>";
        // bubbles config
        $graphs = [
            [
                'balloonText' => sprintf($balloonText, $selectColumns[0]['title']),
                'bullet'      => 'circle',
                'id'          => 'BubbleGraph',
                'lineAlpha'   => 0,
                'lineColor'   => $this->randomColor(),
                'fillAlphas'  => 0,
                'valueField'  => 'value0',
                'xField'      => 'x',
                'yField'      => 'y',
            ],
        ];
        $arrayOutput['dataProvider'] = $chartData;
        $arrayOutput['graphs']       = $graphs;

        return $arrayOutput;
    }

    /**
     * {@inheritdoc}
     */
    public function mergeResults(array $results, $graphType, array $options)
    {
        $mainResults = array_shift($results);

        $assocKeyedDataProvider = [];
        foreach ($mainResults['dataProvider'] as $dataProviderItem) {
            $assocKeyedDataProvider[$dataProviderItem['y']] = $dataProviderItem;
        }
        $mainResults['dataProvider'] = $assocKeyedDataProvider;

        foreach ($results as $resultIndex => $result) {
            foreach ($result['dataProvider'] as $dataProviderItem) {
                if (isset($mainResults['dataProvider'][$dataProviderItem['y']])) {
                    foreach ($dataProviderItem as $itemKey => $value) {
                        if (strpos($itemKey, 'value') !== false) {
                            $newKey                                                       = $resultIndex.'_'.$itemKey;
                            $mainResults['dataProvider'][$dataProviderItem['y']][$newKey] = $value;
                        }
                        if ($itemKey === 'y') {
                            $newKey                                                       = $itemKey.$resultIndex;
                            $mainResults['dataProvider'][$dataProviderItem['y']][$newKey] = $value;
                        }
                        if (stripos($itemKey, 'title') !== false) {
                            $newKey                                                       = $itemKey.$resultIndex;
                            $mainResults['dataProvider'][$dataProviderItem['y']][$newKey] = $value;
                        }
                    }
                }
            }

            foreach ($result['graphs'] as &$graph) {
                $graph['valueField'] = $resultIndex.'_'.$graph['valueField'];
                $graph['id']         = $resultIndex.'_'.$graph['id'];
                $graph['yField']     = $graph['yField'].$resultIndex;
                $graph['xField']     = $graph['xField'].$resultIndex;
                if (isset($graph['balloonText'])) {
                    $graph['balloonText'] = str_replace(
                        ['[[xTitle]]', '[[yTitle]]'],
                        ["[[xTitle{$resultIndex}]]", "[[yTitle{$resultIndex}]]"],
                        $graph['balloonText']
                    );
                }
            }
            $mainResults['graphs'] = array_merge($mainResults['graphs'], $result['graphs']);
        }
        $mainResults['graphs']       = array_reverse($mainResults['graphs']);
        $mainResults['dataProvider'] = array_values($mainResults['dataProvider']);

        return $mainResults;
    }
}

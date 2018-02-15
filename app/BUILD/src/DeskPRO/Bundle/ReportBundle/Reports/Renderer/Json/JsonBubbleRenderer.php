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

use DeskPRO\Bundle\ReportBundle\Reports\ResultMetadata;

/**
 * Class JsonBubbleRenderer.
 */
class JsonBubbleRenderer extends AbstractJsonChartRenderer
{
    /**
     * Constructor.
     *
     * @param JsonValueRenderer $valueRenderer
     */
    public function __construct(JsonValueRenderer $valueRenderer)
    {
        $this->valueRenderer = $valueRenderer;
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
            'categoryAxis' => [
                'gridPosition' => 'start',
                'axisAlpha'    => 0,
                'gridAlpha'    => 0,
                'position'     => 'left',
            ],
            'valueAxes' => [
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

        $selectColumns = $metadata->getSelectColumns();

        // this gonna be y axis
        $groupYColumns = $metadata->getGroupYColumns();

        if ($groupYColumns && $groupYColumns[0]) {
            $columnConfig        = $groupYColumns[0];
            $groupYColumnId      = $columnConfig['groupResultId'] - 1;
            $groupYColumnTitleId = $columnConfig['resultId'] - 1;
            $yAxis['title']      = $columnConfig['title'];
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
            $columnConfig        = $groupXColumns[0];
            $groupXColumnId      = $columnConfig['groupResultId'] - 1;
            $groupXColumnTitleId = $columnConfig['resultId'] - 1;
            $xAxis['title']      = $columnConfig['title'];
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
            $chartData[] = [
                'value'  => $row[0],
                'y'      => $row[4],
                'x'      => $row[2],
                'yTitle' => $row[$groupYColumnTitleId],
                'xTitle' => $row[$groupXColumnTitleId],
            ];
        }

        $balloonText = "<div style='margin:5px;'>%s:<b>[[xTitle]]</b><br>%s:<b>[[yTitle]]</b><br>%s:<b>[[value]]</b></div>";
        // bubbles config
        $graphs = [
            [
                'balloonText' => sprintf($balloonText, 'X', 'Y', $selectColumns[0]['title']),
                'bullet'      => 'circle',
                'id'          => 'AmGraph-1',
                'lineAlpha'   => 0,
                'lineColor'   => $this->randomColor(),
                'fillAlphas'  => 0,
                'valueField'  => 'value',
                'xField'      => 'x',
                'yField'      => 'y',
            ],
        ];
        $arrayOutput['dataProvider'] = $chartData;
        $arrayOutput['graphs']       = $graphs;

        return $arrayOutput;
    }
}

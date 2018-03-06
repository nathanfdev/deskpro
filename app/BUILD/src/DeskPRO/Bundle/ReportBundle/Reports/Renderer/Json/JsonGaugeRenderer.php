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
 * Class JsonGaugeRenderer.
 */
class JsonGaugeRenderer extends AbstractJsonChartRenderer
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
        return self::TYPE_GAUGE;
    }

    protected function getValue(array $rows, ResultMetadata $metadata)
    {
        foreach ($metadata->getSelectColumns() as $column) {
            if ($column['title'] === 'stat_value') {
                return $this->renderCellValue($rows[0], $column, $metadata) ?: 0;
            }
        }

        return 0;
    }

    protected function getTotalValue(array $rows, ResultMetadata $metadata)
    {
        foreach ($metadata->getSelectColumns() as $column) {
            if ($column['title'] === 'stat_total') {
                return $this->renderCellValue($rows[0], $column, $metadata);
            }
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    protected function doRender(array $rows, ResultMetadata $metadata, array $options = [])
    {
        if (!$rows) {
            return;
        }
        $statValue = $this->getValue($rows, $metadata);
        $statTotal = $this->getTotalValue($rows, $metadata);

        if ($statValue > $statTotal || (!$statTotal && $statValue > 100)) {
            $statTotal = ceil($statValue % 100) * 100;
        }

        //initial output array
        $output = [
            'type'  => 'gauge',
            'theme' => 'none',
            'axes'  => [
                [
                    'topTextFontSize'  => 20,
                    'topTextYOffset'   => 70,
                    'axisColor'        => $this->randomColor(),
                    'axisThickness'    => 1,
                    'endValue'         => $statTotal ?: 100,
                    'gridInside'       => true,
                    'inside'           => true,
                    'radius'           => '50%',
                    'valueInterval'    => $statTotal ? ceil($statTotal / 5) : 10,
                    'tickColor'        => $this->randomColor(),
                    'startAngle'       => -90,
                    'endAngle'         => 90,
                    'unit'             => $statTotal ? '' : '%',
                    'bandOutlineAlpha' => 0,
                    'bands'            => [
                        [
                            'color'         => $this->randomColor(),
                            'endValue'      => $statTotal ?: 100,
                            'innerRadius'   => '105%',
                            'radius'        => '170%',
                            'gradientRatio' => [0.5, 0, -0.5],
                            'startValue'    => 0,
                        ],
                        [
                            'color'         => $this->randomColor(),
                            'endValue'      => $statValue,
                            'innerRadius'   => '105%',
                            'radius'        => '170%',
                            'gradientRatio' => [0.5, 0, -0.5],
                            'startValue'    => 0,
                        ],
                    ],
                ],
            ],
            'arrows' => [
                [
                    'alpha'       => 1,
                    'innerRadius' => '35%',
                    'nailRadius'  => 0,
                    'radius'      => '170%',
                    'value'       => $statValue,
                ],
            ],
        ];

        return $output;
    }

    public function mergeResults(array $results)
    {
        return reset($results);
    }
}

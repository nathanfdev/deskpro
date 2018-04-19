<?php

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
                $value = $column['resultId'] ? $rows[0][$column['resultId'] - 1] : 0;

                return $this->valueRenderer->renderValue($value, 'numberraw', $metadata);
            }
        }

        return 0;
    }

    protected function getTotalValue(array $rows, ResultMetadata $metadata)
    {
        foreach ($metadata->getSelectColumns() as $column) {
            if ($column['title'] === 'stat_total') {
                $value = $column['resultId'] ? $rows[0][$column['resultId'] - 1] : 0;

                return $this->valueRenderer->renderValue($value, 'numberraw', $metadata);
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

        if ($statTotal && $statValue && $statValue > $statTotal) {
            $statValue = $statTotal;
        }
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

    public function mergeResults(array $results, array $options)
    {
        return reset($results);
    }
}

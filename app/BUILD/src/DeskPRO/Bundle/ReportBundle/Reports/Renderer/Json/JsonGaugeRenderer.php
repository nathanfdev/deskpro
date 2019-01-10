<?php

namespace DeskPRO\Bundle\ReportBundle\Reports\Renderer\Json;

use DeskPRO\Bundle\ReportBundle\Reports\ResultMetadata;
use Symfony\Bundle\FrameworkBundle\Templating\Helper\AssetsHelper;

/**
 * Class JsonGaugeRenderer.
 */
class JsonGaugeRenderer extends AbstractJsonChartRenderer
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
        return self::TYPE_GAUGE;
    }

    protected function getValue(array $rows, ResultMetadata $metadata)
    {
        return $this->getRowVal('stat_value', $rows, $metadata, 'numberraw');
    }

    protected function getTotalValue(array $rows, ResultMetadata $metadata)
    {
        return $this->getRowVal('stat_total', $rows, $metadata, 'numberraw');
    }

    protected function getRowVal($name, array $rows, ResultMetadata $metadata, $useRenderer = null)
    {
        foreach ($metadata->getSelectColumns() as $column) {
            if ($column['title'] === $name) {
                return $this->renderCellValue($rows[0], $column, $metadata, $useRenderer);
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
        $statTotal = $this->getTotalValue($rows, $metadata) ?: 100;

        if ($statTotal && $statValue && $statValue > $statTotal) {
            $statValue = $statTotal;
        }

        $unitLeft  = $this->getRowVal('unit_left', $rows, $metadata);
        $unitRight = $this->getRowVal('unit_left', $rows, $metadata);
        $tipText   = $this->getRowVal('tooltip_text', $rows, $metadata);

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
                    'valueInterval'    => ceil($statTotal / 5),
                    'tickColor'        => $this->randomColor(),
                    'startAngle'       => -90,
                    'endAngle'         => 90,
                    'unit'             => $unitLeft ?: $unitRight ?: '',
                    'unitPosition'     => $unitRight ? 'right' : 'left',
                    'bandOutlineAlpha' => 0,
                    'usePrefixes'      => true,
                    'bands'            => [
                        [
                            'color'         => $this->randomColor(),
                            'endValue'      => $statTotal ?: 100,
                            'balloonText'   => $tipText ?: '',
                            'innerRadius'   => '105%',
                            'radius'        => '170%',
                            'gradientRatio' => [0.5, 0, -0.5],
                            'startValue'    => 0,
                        ],
                        [
                            'color'         => $this->randomColor(),
                            'endValue'      => $statValue,
                            'balloonText'   => $tipText ?: '',
                            'innerRadius'   => '105%',
                            'radius'        => '170%',
                            'gradientRatio' => [0.5, 0, -0.5],
                            'startValue'    => 0,
                        ],
                    ],
                ],
            ],
            'balloon' => [
                'adjustBorderColor' => true,
                'color'             => '#000000',
                'cornerRadius'      => 5,
                'fillColor'         => '#FFFFFF',
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

    /**
     * {@inheritdoc}
     */
    public function mergeResults(array $results, $graphType, array $options)
    {
        return reset($results);
    }
}

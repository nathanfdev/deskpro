<?php

namespace DeskPRO\Bundle\ReportBundle\Reports\Renderer\Json;

/**
 * Class JsonAreaRenderer.
 */
class JsonAreaRenderer extends AbstractJsonChartRenderer
{
    /**
     * @var array
     */
    protected $options = [
        'type'          => 'line',
        'lineThickness' => 1,
        'bullet'        => 'round',
        'bulletSize'    => 4,
        'fillAlphas'    => 0.6,
    ];

    /**
     * {@inheritdoc}
     */
    public static function getOutputFormat()
    {
        return self::TYPE_AREA;
    }
}

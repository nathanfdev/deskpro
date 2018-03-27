<?php

namespace DeskPRO\Bundle\ReportBundle\Reports\Renderer\Json;

/**
 * Class JsonLineRenderer.
 */
class JsonLineRenderer extends AbstractJsonChartRenderer
{
    /**
     * @var array
     */
    protected $options = [
        'lineThickness' => 2,
        'bullet'        => 'round',
        'bulletSize'    => 6,
    ];

    /**
     * {@inheritdoc}
     */
    public static function getOutputFormat()
    {
        return self::TYPE_LINE;
    }
}

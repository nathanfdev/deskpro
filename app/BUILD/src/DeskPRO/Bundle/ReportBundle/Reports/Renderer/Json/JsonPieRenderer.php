<?php

namespace DeskPRO\Bundle\ReportBundle\Reports\Renderer\Json;

/**
 * Class JsonPieRenderer.
 */
class JsonPieRenderer extends AbstractJsonChartRenderer
{
    /**
     * {@inheritdoc}
     */
    public static function getOutputFormat()
    {
        return self::TYPE_PIE;
    }
}

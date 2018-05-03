<?php

namespace DeskPRO\Bundle\ReportBundle\Reports\Renderer\Html;

/**
 * Class HtmlAreaRenderer.
 */
class HtmlAreaRenderer extends AbstractHtmlChartsRenderer
{
    /**
     * @var string
     */
    protected $options = '
        graph.type = "line";
        graph.lineThickness = 1;
        graph.bullet = "round";
        graph.bulletSize = 4;
        graph.fillAlphas = 0.6;
    ';

    /**
     * {@inheritdoc}
     */
    public static function getOutputFormat()
    {
        return self::TYPE_AREA;
    }
}

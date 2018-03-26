<?php

namespace DeskPRO\Bundle\ReportBundle\Reports\Renderer\Html;

/**
 * Class HtmlBarRenderer.
 */
class HtmlBarRenderer extends AbstractHtmlChartsRenderer
{
    /**
     * @var string
     */
    protected $options = '
        graph.type = "column";
        graph.fillAlphas = 1;
    ';

    /**
     * {@inheritdoc}
     */
    public static function getOutputFormat()
    {
        return self::TYPE_BAR;
    }
}

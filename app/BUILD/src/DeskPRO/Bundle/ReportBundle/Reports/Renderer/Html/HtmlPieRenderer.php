<?php

namespace DeskPRO\Bundle\ReportBundle\Reports\Renderer\Html;

/**
 * Class HtmlPieRenderer.
 */
class HtmlPieRenderer extends AbstractHtmlChartsRenderer
{
    /**
     * {@inheritdoc}
     */
    public static function getOutputFormat()
    {
        return self::TYPE_PIE;
    }
}

<?php

namespace DeskPRO\Bundle\ReportBundle\Reports\Renderer\Html;

use DeskPRO\Bundle\ReportBundle\Reports\Renderer\AbstractRenderer;

/**
 * Class AbstractHtmlRenderer.
 */
abstract class AbstractHtmlRenderer extends AbstractRenderer
{
    /**
     * {@inheritdoc}
     */
    public static function getContentType()
    {
        return 'text/html';
    }

    /**
     * {@inheritdoc}
     */
    public static function getExtension()
    {
        return 'html';
    }

    /**
     * {@inheritdoc}
     */
    protected function implodeSplitOutput(array $output)
    {
        return implode("\n\n", $output);
    }

    /**
     * {@inheritdoc}
     */
    public function renderSplitOutputWithHeader($header, $body)
    {
        return '<h3 class="report-split-header">'.$header.'</h3>'."\n$body";
    }
}

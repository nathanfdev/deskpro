<?php

namespace DeskPRO\Bundle\ReportBundle\Reports\Renderer\Html;

use DeskPRO\Bundle\ReportBundle\Reports\Renderer\AbstractValueRenderer;

/**
 * Value renderer for HTML output.
 */
class HtmlValueRenderer extends AbstractValueRenderer
{
    /**
     * {@inheritdoc}
     */
    protected function renderNull()
    {
        return '<span class="null">None</span>';
    }

    /**
     * {@inheritdoc}
     */
    protected function renderBoolean($value)
    {
        if ($value) {
            return '<span class="true">Y</span>';
        } else {
            return '<span class="false">N</span>';
        }
    }

    /**
     * {@inheritdoc}
     */
    public function escapeValue($value)
    {
        return htmlspecialchars($value);
    }
}

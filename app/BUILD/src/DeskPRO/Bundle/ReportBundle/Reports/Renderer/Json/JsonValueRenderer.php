<?php

namespace DeskPRO\Bundle\ReportBundle\Reports\Renderer\Json;

use DeskPRO\Bundle\ReportBundle\Reports\Renderer\AbstractValueRenderer;

/**
 * Value renderer for HTML output.
 */
class JsonValueRenderer extends AbstractValueRenderer
{
    /**
     * {@inheritdoc}
     */
    protected function renderNull()
    {
        return '';
    }

    /**
     * {@inheritdoc}
     */
    protected function renderBoolean($value)
    {
        if ($value) {
            return true;
        } else {
            return false;
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

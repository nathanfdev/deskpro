<?php

namespace DeskPRO\Bundle\ReportBundle\Reports\Renderer;

/**
 * Value renderer for text-based output.
 */
class TextValueRenderer extends AbstractValueRenderer
{
    /**
     * {@inheritdoc}
     */
    protected function renderNull()
    {
        return 'None';
    }

    /**
     * {@inheritdoc}
     */
    protected function renderBoolean($value)
    {
        return $value ? 'Y' : 'N';
    }

    /**
     * {@inheritdoc}
     */
    public function escapeValue($value)
    {
        return $value;
    }
}

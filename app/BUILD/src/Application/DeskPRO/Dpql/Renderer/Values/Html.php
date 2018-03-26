<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\Dpql\Renderer\Values;

/**
 * Value renderer for HTML output.
 */
class Html extends AbstractValues
{
    /**
     * Renders a null value.
     *
     * @return string
     */
    protected function _renderNull()
    {
        return '<span class="null">None</span>';
    }

    /**
     * Renders a boolean value.
     *
     * @param bool $value
     *
     * @return string
     */
    protected function _renderBoolean($value)
    {
        if ($value) {
            return '<span class="true">Y</span>';
        } else {
            return '<span class="false">N</span>';
        }
    }

    /**
     * Escapes the value for direct output.
     *
     * @param string $value
     *
     * @return string
     */
    public function escapeValue($value)
    {
        return htmlspecialchars($value);
    }
}

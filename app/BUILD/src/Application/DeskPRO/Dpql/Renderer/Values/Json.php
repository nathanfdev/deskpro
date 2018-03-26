<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\Dpql\Renderer\Values;

/**
 * Value renderer for HTML output.
 */
class Json extends AbstractValues
{
    /**
     * Renders a null value.
     *
     * @return string
     */
    protected function _renderNull()
    {
        return '';
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
            return true;
        } else {
            return false;
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

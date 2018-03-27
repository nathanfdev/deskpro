<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\Dpql\Renderer\Values;

/**
 * Value renderer for text-based output.
 */
class Text extends AbstractValues
{
    /**
     * Renders a null value.
     *
     * @return string
     */
    protected function _renderNull()
    {
        return 'None';
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
        return $value ? 'Y' : 'N';
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
        return $value;
    }
}

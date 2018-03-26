<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\Dpql\Placeholder;

/**
 * Place holder for a non-restricted date.
 */
class Ever extends AbstractDateRange
{
    /**
     * Gets the date range components (printable, start, end).
     *
     * @return array
     */
    protected function _getDateRange()
    {
        return ['ever'];
    }
}

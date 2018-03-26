<?php

/**
 * Orb.
 *
 * @category Input
 */

namespace Orb\Input\Cleaner\CleanerPlugin;

use Orb\Input\Cleaner\Cleaner;

/**
 * A cleaner plugin registeres typenames and callbacks.
 */
interface CleanerPlugin
{
    /**
     * Array of typename=>methodname.
     *
     * @return array
     */
    public function getCleanerTypes();

    /**
     * @return string
     */
    public function getCleanerId();

    public function cleanValue($value, $type, array $options, Cleaner $cleaner);
}

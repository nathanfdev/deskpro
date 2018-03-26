<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\NewSettings;

/**
 * Knows how to load some representation of settings from somewhere.
 */
interface SettingsLoaderInterface
{
    /**
     * Must return an array of it's representation of the settings. SHOULD use a cache as this method may be called
     * many times in a single request, and the method contract requires the ability to force a reload of the data,
     * implying the same data is returned each time $force === false.
     *
     * @param bool $force true if cache should be invalidated and forced to refresh the data
     *
     * @return array
     */
    public function load($force = false);
}

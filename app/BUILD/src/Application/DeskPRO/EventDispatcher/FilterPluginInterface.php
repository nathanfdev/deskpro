<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\EventDispatcher;

/**
 * An event that is filterable is able to tell if a Plugin should be fired based
 * on whatever criteria.
 */
interface FilterPluginInterface
{
    /**
     * @param Plugin $plugins
     *
     * @return bool
     */
    public function filterPlugins($plugin);
}

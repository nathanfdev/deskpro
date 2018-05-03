<?php

/**
 * Orb.
 *
 * @category Util
 */

namespace Orb\Helper;

interface ShortCallableInterface
{
    /**
     * Get an array of name=>method that will be registered on the helper.
     * When calling helper->name(), the registered method will be called instead.
     */
    public function getShortCallableNames();
}

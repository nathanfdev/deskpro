<?php

/**
 * Orb.
 *
 * @category Util
 */

namespace Orb\Util;

/**
 * An object that implements this interface is able to tell about its own capabilities.
 *
 * For example, useful with adapters when various features might not be supported between each adapter
 * and implementation code will need to check.
 *
 * Here's an example usage:
 * <code>
 * $searcher = $this->getSearchAdapter();
 * if (!$searcher->isCapable(Searcher::FIND_TAGS)) {
 *     die('Sorry, this feature is not available');
 * }
 * </code>
 *
 * Actual capabilities are usually represented using strings, but it's recommended
 * these strings be defined as class constants.
 */
interface CapabilityInformerInterface
{
    /**
     * Returns an array of all capabilities.
     *
     * @return array
     */
    public function getCapabilities();

    /**
     * Check if this object is capable of a specific thing.
     *
     * @param mixed $capability
     *
     * @return bool
     */
    public function isCapable($capability);
}

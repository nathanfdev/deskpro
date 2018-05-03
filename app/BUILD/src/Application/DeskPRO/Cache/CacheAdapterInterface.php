<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\Cache;

/**
 * All cache adapters implement this interface. A cache adapter must return the value exactly as it receives it. So
 * the adapter must store the cached value in such a way that it can always (obv including future requests if need be)
 * return the same php value back.
 *
 * If we set an array, an object, etc, it must be returned equally. Must be a valid php value, but cannot be a callable,
 * or a closure.
 *
 * Recommended to store scalars or plain scalar arrays for best results.
 */
interface CacheAdapterInterface
{
    /**
     * Set a value onto the cache.
     *
     * @param $key
     * @param $val
     *
     * @return mixed
     */
    public function set($key, $val);

    /**
     * True if cache appears to have a value for the key.
     *
     * @param $key
     *
     * @return bool
     */
    public function has($key);

    /**
     * Gets the value for a key, in the same form as it was set (returns arrays, objects, scalars, etc).
     *
     * @param $key
     *
     * @return null|mixed
     */
    public function get($key);

    /**
     * Removes the value and unsets the key, should be safe to call even if key doesn't exist.
     *
     * @param $key
     *
     * @return mixed
     */
    public function delete($key);
}

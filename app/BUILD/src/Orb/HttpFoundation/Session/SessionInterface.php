<?php

/**
 * Orb.
 */

namespace Orb\HttpFoundation\Session;

/**
 * A session interface.
 */
interface SessionInterface extends \ArrayAccess, \IteratorAggregate
{
    /**
     * Checks if a data item is defined.
     *
     * @param string $name The data item name
     *
     * @return bool
     */
    public function has($name);

    /**
     * Returns a data item.
     *
     * @param string $name    The attribute name
     * @param mixed  $default The default value
     *
     * @return mixed
     */
    public function get($name, $default = null);

    /**
     * Sets a data item.
     *
     * @param string $name
     * @param mixed  $value
     */
    public function set($name, $value);

    /**
     * Returns data.
     *
     * @return array
     */
    public function getAllData();

    /**
     * Sets data.
     *
     * @param array $data Attributes
     */
    public function setAllData($data);

    /**
     * Removes a data item.
     *
     * @param string $name
     */
    public function remove($name);

    /**
     * Removes all set data.
     */
    public function removeAllData();
}

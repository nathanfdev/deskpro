<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\Cache\Adapter;

use Application\DeskPRO\Cache\CacheAdapterInterface;

/**
 * Just storing the cache during this request in a normal PHP array, the simplest cache possible.
 */
class SimpleArrayCache implements CacheAdapterInterface
{
    /**
     * @var array the cache
     */
    public $cache;

    public function __construct()
    {
        $this->cache = [];
    }

    public function set($key, $val)
    {
        $this->cache[$key] = $val;
    }

    public function get($key)
    {
        if ($this->has($key)) {
            return $this->cache[$key];
        }
    }

    public function delete($key)
    {
        if ($this->has($key)) {
            unset($this->cache[$key]);
        }
    }

    public function has($key)
    {
        if (!$this->cache) {
            return false;
        }

        return array_key_exists($key, $this->cache);
    }
}

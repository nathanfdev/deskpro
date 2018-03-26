<?php

namespace DeskPRO\Component\Presenter;

use Orb\Util\Strings;

abstract class PresenterValue implements \ArrayAccess
{
    private $value = [];

    private $id_normal_cache        = [];
    private $id_getter_cache        = [];
    private $id_target_getter_cache = [];
    private $cache_field_names      = [];
    private $cache_field_values     = [];

    private $passthrough_whitelist = [];
    private $passthrough_blacklist = [];

    private $getter_passthrough = null;
    private $call_passthrough   = null;
    private $array_passthrough  = null;

    /**
     * Sets the value this presenter wraps.
     *
     * @param mixed $value
     *
     * @return $this
     */
    protected function setPresenterValue($value)
    {
        $this->value = $value;

        return $this;
    }

    /**
     * Sets which fields from getters that should be cached instead of being re-called.
     *
     * @param $fields...
     *
     * @return $this
     */
    protected function cacheFields($fields)
    {
        $args = func_get_args();
        foreach ($args as $a) {
            if (!is_array($a)) {
                $a = [$a];
            }
            foreach ($a as $id) {
                $this->cache_field_names[$id] = true;
            }
        }

        return $this;
    }

    /**
     * Sets fields that should be passed-through to the back-end value if no local getter is set.
     *
     * @param $fields...
     *
     * @return $this
     */
    protected function passthroughFields($fields)
    {
        $args = func_get_args();
        foreach ($args as $a) {
            if (!is_array($a)) {
                $a = [$a];
            }
            foreach ($a as $id) {
                if ($id[0] == '!') {
                    $id = substr($id, 1);
                    if ($id == '*') {
                        $this->passthrough_blacklist['*'] = true;
                    } else {
                        $id2                               = $this->normalizeId($id);
                        $this->passthrough_blacklist[$id]  = true;
                        $this->passthrough_blacklist[$id2] = true;
                    }
                } else {
                    if ($id == '*') {
                        $this->passthrough_whitelist['*'] = true;
                    } else {
                        $id2                               = $this->normalizeId($id);
                        $this->passthrough_whitelist[$id]  = true;
                        $this->passthrough_whitelist[$id2] = true;
                    }
                }
            }
        }
    }

    /**
     * Enables passthrough to the back-end value when calling getters.
     *
     * @return $this
     */
    protected function enableGetterPassthrough()
    {
        $this->getter_passthrough = true;

        return $this;
    }

    /**
     * Enables passthrough to the back-end value when no such method exists.
     *
     * @return $this
     */
    protected function enableCallPassthrough()
    {
        $this->call_passthrough = true;

        return $this;
    }

    /**
     * Enables passthrough to the back-end value when accessing arrays.
     *
     * @return $this
     */
    protected function enableArrayPassthrough()
    {
        $this->array_passthrough = true;

        return $this;
    }

    private function getLocalGetter($id)
    {
        $id = $this->normalizeId($id);
        if (isset($this->id_getter_cache[$id])) {
            if ($this->id_getter_cache[$id] === false) {
                return;
            }

            return $this->id_getter_cache[$id];
        }

        $name = 'present'.ucfirst(Strings::underscoreToCamelCase($id));
        if (method_exists($this, $name)) {
            $this->id_getter_cache[$id] = $name;

            return $name;
        }

        $this->id_getter_cache[$id] = false;

        return;
    }

    private function getTargetGetter($id)
    {
        $id = $this->normalizeId($id);
        if (isset($this->id_target_getter_cache[$id])) {
            if ($this->id_target_getter_cache[$id] === false) {
                return;
            }

            return $this->id_target_getter_cache[$id];
        }

        if (substr($id, 0, 2) !== 'is') {
            $name = 'get'.ucfirst(Strings::underscoreToCamelCase($id));
        } else {
            $name = Strings::underscoreToCamelCase($id);
        }

        if (method_exists($this->value, $name)) {
            $this->id_target_getter_cache[$id] = $name;

            return $name;
        }

        $this->id_target_getter_cache[$id] = false;

        return;
    }

    public function __call($name, array $args)
    {
        if (strpos($name, 'get') === 0) {
            $n = substr($name, 4);
        } else {
            $n = $name;
        }

        if ($this->offsetExists($n)) {
            return $this->offsetGet($n);
        }

        if ($this->call_passthrough) {
            if (
                (isset($this->passthrough_whitelist['*']) || isset($this->passthrough_whitelist[$n]) || isset($this->passthrough_whitelist[$name]))
                && (!isset($this->passthrough_blacklist['*']) || !isset($this->passthrough_blacklist[$n]) || !isset($this->passthrough_blacklist[$name]))
            ) {
                return $this->value->__call($name, $args);
            }
        }

        throw new \BadMethodCallException();
    }

    public function offsetExists($offset)
    {
        $id = $this->normalizeId($offset);
        if (isset($this->cache_field_values[$id])) {
            return true;
        }

        if ($this->getLocalGetter($id)) {
            return true;
        }

        if ($this->array_passthrough) {
            if (isset($this->value[$offset])) {
                return true;
            }
            if (isset($this->value[$id])) {
                return true;
            }
        }

        if ($this->getter_passthrough && $this->getTargetGetter($id)) {
            if (
                (isset($this->passthrough_whitelist['*']) || isset($this->passthrough_whitelist[$id]) || isset($this->passthrough_whitelist[$offset]))
                && (!isset($this->passthrough_blacklist['*']) || !isset($this->passthrough_blacklist[$id]) || !isset($this->passthrough_blacklist[$offset]))
            ) {
                return true;
            }
        }

        return false;
    }

    public function offsetGet($offset)
    {
        $id = $this->normalizeId($offset);
        if (isset($this->cache_field_values[$id])) {
            return $this->cache_field_values[$id];
        }

        $ret                           = $this->doGetId($id, $offset);
        $this->cache_field_values[$id] = $ret;

        return $ret;
    }

    private function doGetId($id, $offset)
    {
        if ($m = $this->getLocalGetter($id)) {
            return $this->$m();
        }

        if ($this->array_passthrough && isset($this->value[$offset])) {
            if (
                (isset($this->passthrough_whitelist['*']) || isset($this->passthrough_whitelist[$id]) || isset($this->passthrough_whitelist[$offset]))
                && (!isset($this->passthrough_blacklist['*']) || !isset($this->passthrough_blacklist[$id]) || !isset($this->passthrough_blacklist[$offset]))
            ) {
                if (isset($this->value[$offset])) {
                    return $this->value[$offset];
                }
                if (isset($this->value[$id])) {
                    return $this->value[$id];
                }
            }
        }

        if ($this->getter_passthrough && $m = $this->getTargetGetter($id)) {
            return $this->value->$m();
        }

        return;
    }

    public function offsetSet($offset, $value)
    {
        throw new \BadMethodCallException();
    }

    public function offsetUnset($offset)
    {
        throw new \BadMethodCallException();
    }

    protected function normalizeId($id)
    {
        if (isset($this->id_normal_cache[$id])) {
            return $this->id_normal_cache[$id];
        }
        $this->id_normal_cache[$id] = Strings::camelCaseToUnderscore($id);

        return $this->id_normal_cache[$id];
    }
}

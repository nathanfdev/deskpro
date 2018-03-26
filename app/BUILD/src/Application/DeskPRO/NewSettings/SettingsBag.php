<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\NewSettings;

/**
 * The SettingsBag acts like an immutable array, and also offers an API with methods like has('key') and get('key', 'default').
 */
class SettingsBag implements \ArrayAccess, \IteratorAggregate, \Countable, \Serializable
{
    /**
     * @var array
     */
    protected $settings;

    public function __construct(array $settings = [])
    {
        $this->setArray($settings);
    }

    public function toArray()
    {
        return $this->settings;
    }

    public function setArray(array $settings)
    {
        $this->settings = $settings;
    }

    public function has($key)
    {
        return array_key_exists($key, $this->settings);
    }

    public function get($key, $default = null)
    {
        return $this->has($key) ? $this->settings[$key] : $default;
    }

    public function getBool($key, $default = false)
    {
        return $this->has($key) ? (bool) $this->settings[$key] : (bool) $default;
    }

    public function getSerializedArray($key, $default = [])
    {
        if (!$this->has($key)) {
            return $default;
        }
        $arr = is_array($this->get($key)) ? $this->get($key) : @unserialize($this->get($key));
        if (!is_array($arr)) {
            return $default;
        }

        return $arr;
    }

    /**
     * {@inheritdoc}
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->settings);
    }

    /**
     * {@inheritdoc}
     */
    public function offsetExists($offset)
    {
        return $this->has($offset);
    }

    /**
     * {@inheritdoc}
     */
    public function offsetGet($offset)
    {
        return $this->get($offset);
    }

    /**
     * {@inheritdoc}
     */
    public function offsetSet($offset, $value)
    {
        throw new \LogicException('cannot set a setting in this way. instead, change the underlying source of the setting and get a fresh settings bag by forcing a reload of settings on the settings resolver');
    }

    /**
     * {@inheritdoc}
     */
    public function offsetUnset($offset)
    {
        throw new \LogicException(
            'cannot unset a setting in this way. instead, remove from the underlying source of the setting and get a fresh settings bag by forcing a reload of settings on the settings resolver'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function serialize()
    {
        return serialize($this->settings);
    }

    /**
     * {@inheritdoc}
     */
    public function unserialize($serialized)
    {
        return unserialize($serialized);
    }

    /**
     * {@inheritdoc}
     */
    public function count()
    {
        return count($this->settings);
    }

    /**
     * Seperating setting names by dots "." is popular. We can use grouping to subset a settings bag and get the
     * result array of settings that fit inside that subset.
     *
     * eg. new SettingsBag(array('core.register' => 1));
     *
     * This method with the input $group = 'core' will return:
     *
     * array('register' => 1)
     *
     * Set the "short" flag to false to not cut off the group part of the setting name, getting:
     *
     * array('core.register' => 1)
     *
     * @param string $group the group prefix
     * @param bool   $short true to cut the group name out of the result array keys
     *
     * @return array
     */
    public function getGroup($group, $short = true)
    {
        $ret = [];

        $group_dot = $group.'.';
        $len       = strlen($group_dot);

        foreach ($this->settings as $k => $v) {
            if (substr($k, 0, $len) === $group_dot) {
                $ret[$short ? substr($k, $len) : $k] = $v;
            }
        }

        return $ret;
    }
}

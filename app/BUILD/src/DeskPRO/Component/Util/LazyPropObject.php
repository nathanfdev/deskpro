<?php

namespace DeskPRO\Component\Util;

/**
 * Lazy load properties on an object with getters.
 */
class LazyPropObject
{
    /**
     * @var LazyValue[]
     */
    private $props;

    /**
     * LazyPropObject constructor.
     *
     * @param array $props Array of propName => propLoader
     */
    public function __construct(array $props)
    {
        foreach ($props as $propName => $propLoader) {
            $this->add($propName, $propLoader);
        }
    }

    /**
     * @param string             $prop
     * @param callable|LazyValue $loader
     */
    public function add($prop, $loader)
    {
        $this->props[$prop] = new LazyValue($loader, [$this]);
    }

    /**
     * @param string $prop
     *
     * @return bool
     */
    public function isDefined($prop)
    {
        return isset($this->props[$prop]);
    }

    /**
     * @param string $prop
     *
     * @return bool
     */
    public function isLoaded($prop)
    {
        if (!isset($this->props[$prop])) {
            throw new \OutOfBoundsException();
        }

        return $this->props[$prop]->isLoaded();
    }

    /**
     * @param string $prop
     *
     * @return mixed
     */
    public function get($prop)
    {
        if (!isset($this->props[$prop])) {
            throw new \OutOfBoundsException();
        }

        $val = $this->props[$prop];

        return $val();
    }

    /**
     * @param string $prop
     *
     * @return mixed
     */
    public function reload($prop)
    {
        if (!isset($this->props[$prop])) {
            throw new \OutOfBoundsException();
        }

        return $this->props[$prop]->reload();
    }

    public function __get($prop)
    {
        return $this->get($prop);
    }

    public function __isset($prop)
    {
        return isset($this->props[$prop]);
    }
}

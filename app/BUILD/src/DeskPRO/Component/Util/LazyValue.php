<?php

namespace DeskPRO\Component\Util;

/**
 * Lazy load a value with a loader function the first time it's accessed.
 */
class LazyValue
{
    /**
     * @var callable
     */
    private $loader;

    /**
     * @var array
     */
    private $loaderParams = [];

    /**
     * @var mixed
     */
    private $value;

    /**
     * @var bool
     */
    private $isLoaded = false;

    /**
     * LazyValue constructor.
     *
     * The loader is passed the following params:
     * - $loaderParams
     * - Current value (if one exists, first time obviously would will be null)
     *
     * @param callable $loader
     * @param array    $loaderParams
     */
    public function __construct($loader, $loaderParams = [])
    {
        $this->loader       = $loader;
        $this->loaderParams = $loaderParams;
    }

    /**
     * @return bool
     */
    public function isLoaded()
    {
        return $this->isLoaded;
    }

    /**
     * Manually load (or re-load) the value.
     *
     * If already loaded, the previous value will be passed to the loader
     * as the first parameter.
     *
     * @return mixed
     */
    public function reload()
    {
        $this->isLoaded     = true;

        return $this->value = call_user_func_array($this->loader, array_merge($this->loaderParams, [$this->value]));
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        if (!$this->isLoaded) {
            $this->reload();
        }

        return $this->value;
    }

    /**
     * @return mixed
     */
    public function __invoke()
    {
        return $this->getValue();
    }
}

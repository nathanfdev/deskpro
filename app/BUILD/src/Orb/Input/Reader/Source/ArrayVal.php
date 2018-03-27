<?php

/**
 * Orb.
 *
 * @category Input
 */

namespace Orb\Input\Reader\Source;

/**
 * A reader source that reads data from a normal array or array-like object.
 */
class ArrayVal implements SourceInterface
{
    /**
     * The array set.
     *
     * @var array
     */
    protected $array;

    /**
     * Create the source.
     *
     * @param  $array The array
     */
    public function __construct($array)
    {
        $this->array = $array;
    }

    /**
     * Get the value of some variable.
     *
     * @param string|array $name    The name of the variable
     * @param mixed        $options Any options there may be
     *
     * @return mixed
     */
    public function getValue($name, $options = null)
    {
        $parts = [];
        if (is_array($name)) {
            $parts = $name;
            $name  = array_shift($parts);
        }

        if (isset($this->array[$name])) {
            $value = $this->array[$name];
        } else {
            $value = null;
        }

        if ($parts) {
            foreach ($parts as $part) {
                if (!is_array($value) or !isset($value[$part])) {
                    $value = null;
                    break;
                }

                $value = $value[$part];
            }
        }

        return $value;
    }

    /**
     * Check if a value of some variable is set.
     *
     * @param string|array $name    The name of the variable
     * @param mixed        $options Any options there may be
     *
     * @return bool
     */
    public function checkIsset($name, $options = null)
    {
        return $this->getValue($name, $options) === null ? false : true;
    }

    /**
     * Get the superglobal name.
     *
     * @return string
     */
    public function getArray()
    {
        return $this->array;
    }

    /**
     * Set the array value.
     *
     * @param array $array
     */
    public function setArray($array)
    {
        $this->array = $array;
    }
}

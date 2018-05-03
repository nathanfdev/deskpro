<?php

namespace DeskPRO\Component\Collections;

/**
 * Like ArrayObject except you pass it a loader function, and the loader function
 * is called to init the object only if it is used.
 *
 * Useful in cases where initialising the object might be expensive, but the object
 * might not actually be used by client code (e.g., imagine an event handler that might
 * ignore it).
 */
class LazyArrayObject implements \IteratorAggregate, \ArrayAccess, \Serializable, \Countable
{
    /**
     * @var callable
     */
    private $loader;

    /**
     * @var array
     */
    private $data;

    /**
     * LazyArrayObject constructor.
     *
     * @param callable $loader The thing that loads the array. MUST return an array
     */
    public function __construct($loader)
    {
        $this->loader = $loader;
    }

    /**
     * Loads the data.
     */
    private function lazyInit()
    {
        if ($this->data !== null) {
            return;
        }

        $arr = call_user_func($this->loader);

        if (!is_array($arr)) {
            throw new \RuntimeException('loader did not return array');
        }

        $this->data = $arr;
    }

    /**
     * Retrieve an external iterator.
     *
     * @link  http://php.net/manual/en/iteratoraggregate.getiterator.php
     *
     * @return \Traversable An instance of an object implementing <b>Iterator</b> or
     *                      <b>Traversable</b>
     */
    public function getIterator()
    {
        $this->lazyInit();

        return new \ArrayIterator($this->data);
    }

    /**
     * Whether a offset exists.
     *
     * @link  http://php.net/manual/en/arrayaccess.offsetexists.php
     *
     * @param mixed $offset <p>
     *                      An offset to check for.
     *                      </p>
     *
     * @return bool true on success or false on failure.
     *              </p>
     *              <p>
     *              The return value will be casted to boolean if non-boolean was returned
     */
    public function offsetExists($offset)
    {
        $this->lazyInit();

        return isset($this->data[$offset]);
    }

    /**
     * Offset to retrieve.
     *
     * @link  http://php.net/manual/en/arrayaccess.offsetget.php
     *
     * @param mixed $offset <p>
     *                      The offset to retrieve.
     *                      </p>
     *
     * @return mixed Can return all value types
     */
    public function offsetGet($offset)
    {
        $this->lazyInit();

        return $this->data[$offset];
    }

    /**
     * Offset to set.
     *
     * @link  http://php.net/manual/en/arrayaccess.offsetset.php
     *
     * @param mixed $offset <p>
     *                      The offset to assign the value to.
     *                      </p>
     * @param mixed $value  <p>
     *                      The value to set.
     *                      </p>
     */
    public function offsetSet($offset, $value)
    {
        $this->lazyInit();
        $this->data[$offset] = $value;
    }

    /**
     * Offset to unset.
     *
     * @link  http://php.net/manual/en/arrayaccess.offsetunset.php
     *
     * @param mixed $offset <p>
     *                      The offset to unset.
     *                      </p>
     */
    public function offsetUnset($offset)
    {
        $this->lazyInit();
        unset($this->data[$offset]);
    }

    /**
     * String representation of object.
     *
     * @link  http://php.net/manual/en/serializable.serialize.php
     *
     * @return string the string representation of the object or null
     */
    public function serialize()
    {
        $this->lazyInit();

        return serialize($this->data);
    }

    /**
     * Constructs the object.
     *
     * @link  http://php.net/manual/en/serializable.unserialize.php
     *
     * @param string $serialized <p>
     *                           The string representation of the object.
     *                           </p>
     */
    public function unserialize($serialized)
    {
        $this->data = unserialize($serialized);
    }

    /**
     * Count elements of an object.
     *
     * @link  http://php.net/manual/en/countable.count.php
     *
     * @return int The custom count as an integer.
     *             </p>
     *             <p>
     *             The return value is cast to an integer
     */
    public function count()
    {
        $this->lazyInit();

        return count($this->data);
    }
}

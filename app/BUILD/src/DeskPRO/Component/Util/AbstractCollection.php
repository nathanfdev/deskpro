<?php

namespace DeskPRO\Component\Util;

/**
 * Base collection.
 *
 * Class AbstractCollection
 */
abstract class AbstractCollection implements \Iterator, \Countable
{
    /**
     * @var array
     */
    protected $collection = [];

    /**
     * {@inheritdoc}
     */
    public function current()
    {
        return current($this->collection);
    }

    /**
     * {@inheritdoc}
     */
    public function next()
    {
        next($this->collection);
    }

    /**
     * {@inheritdoc}
     */
    public function key()
    {
        return key($this->collection);
    }

    /**
     * {@inheritdoc}
     */
    public function valid()
    {
        return current($this->collection);
    }

    /**
     * {@inheritdoc}
     */
    public function rewind()
    {
        reset($this->collection);
    }

    /**
     * {@inheritdoc}
     */
    public function count()
    {
        return count($this->collection);
    }

    /**
     * To array.
     *
     * @return array
     */
    public function toArray()
    {
        return $this->collection;
    }

    /**
     * Get and remove last element of the collection.
     *
     * @return mixed|null
     */
    public function pop()
    {
        return array_pop($this->collection);
    }

    /**
     * @return string
     */
    public function getType()
    {
        return get_called_class();
    }
}

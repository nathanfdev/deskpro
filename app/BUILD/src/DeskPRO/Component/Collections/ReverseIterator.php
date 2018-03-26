<?php

namespace DeskPRO\Component\Collections;

class ReverseIterator implements \Iterator
{
    /**
     * @var array
     */
    private $value;

    /**
     * ReverseIterator constructor.
     *
     * Iterate over an array or Traversable in reverse order.
     * (Note: If you provide a Traversable, it will be converted to an array which might be costly.)
     *
     * @param array|\Traversable $value
     */
    public function __construct($value)
    {
        if (is_array($value)) {
            $this->value = $value;
        } else {
            $this->value = iterator_to_array($value, true);
        }

        end($this->value);
    }

    /**
     * {@inheritdoc}
     */
    public function current()
    {
        return current($this->value);
    }

    /**
     * {@inheritdoc}
     */
    public function next()
    {
        prev($this->value);
    }

    /**
     * {@inheritdoc}
     */
    public function key()
    {
        return key($this->value);
    }

    /**
     * {@inheritdoc}
     */
    public function valid()
    {
        return key($this->value) !== null;
    }

    /**
     * {@inheritdoc}
     */
    public function rewind()
    {
        end($this->value);
    }
}

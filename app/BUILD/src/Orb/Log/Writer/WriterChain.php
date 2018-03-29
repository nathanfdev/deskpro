<?php

/**
 * Orb.
 */

namespace Orb\Log\Writer;

use Orb\Log\LogItem;

/**
 * A writer that calls other writers.
 */
class WriterChain extends AbstractWriter implements \Countable, \IteratorAggregate
{
    /**
     * An array of writers.
     *
     * @var array
     */
    protected $_writers = [];

    /**
     * Add a new filter to the chain.
     *
     * @param FilterInterface $writer
     */
    public function addWriter(AbstractWriter $writer)
    {
        $this->_writers[] = $writer;
    }

    /**
     * Add a new filter to the chain.
     *
     * @param FilterInterface $writer
     */
    public function removeWriter(AbstractWriter $writer)
    {
        if (($k = array_search($writer, $this->_writers, true)) !== false) {
            array_splice($this->_writers, $k, 1);
        }
    }

    /**
     * Get the writers currently set.
     *
     * @return array
     */
    public function getWriters()
    {
        return $this->_writers;
    }

    /**
     * Write a log message.
     *
     * @param LogItem $event
     *
     * @return bool
     */
    public function _write(LogItem $log_item)
    {
        foreach ($this->_writers as $writer) {
            $writer->write($log_item);
        }

        return true;
    }

    /**
     * Perform shutdown activities.
     */
    public function shutdown()
    {
        foreach ($this->_writers as $writer) {
            $writer->shutdown();
        }
    }

    /**
     * Count how many writers there are.
     *
     * @return int
     */
    public function count()
    {
        return count($this->_writers);
    }

    /**
     * @return \ArrayIterator
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->_writers);
    }
}

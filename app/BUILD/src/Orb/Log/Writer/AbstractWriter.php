<?php

/**
 * Orb.
 */

namespace Orb\Log\Writer;

use Orb\Log\LogItem;

/**
 * A writer saves data somewhere.
 */
abstract class AbstractWriter
{
    /**
     * Filter chain applied to the writer.
     *
     * @var Orb\Filter\FilterChain
     */
    protected $_filter_chain = null;

    public function __destruct()
    {
        $this->shutdown();
    }

    /**
     * Get the filter chain instance.
     *
     * @return \Orb\Filter\FilterChain
     */
    public function getFilterChain()
    {
        if ($this->_filter_chain === null) {
            $this->_filter_chain = new \Orb\Filter\FilterChain();
        }

        return $this->_filter_chain;
    }

    /**
     * Add a filter to be applied to every item.
     *
     * @param \Zend\Filter\FilterInterface $filter
     *
     * @return AbstractWriter
     */
    public function addFilter(\Orb\Filter\FilterInterface $filter)
    {
        $this->getFilterChain()->addFilter($filter);

        return $this;
    }

    /**
     * Run filters on the log items.
     *
     * @param LogItem $log_item
     *
     * @return LogItem
     */
    public function filterLogItem(LogItem $log_item)
    {
        // Not initialized, means no filters
        if ($this->_filter_chain === null) {
            return $log_item;
        }

        $log_item = $this->_filter_chain->filter($log_item);

        return $log_item;
    }

    /**
     * Write a log message.
     *
     * @param LogItem $event
     *
     * @return bool
     */
    public function write(LogItem $log_item)
    {
        $log_item = $this->filterLogItem($log_item);

        if (!$log_item) {
            return false;
        }

        $this->_write($log_item);

        return true;
    }

    /**
     * Write a log message.
     *
     * @param LogItem $event
     *
     * @return Writer
     */
    abstract protected function _write(LogItem $log_item);

    /**
     * Perform shutdown activities.
     */
    public function shutdown()
    {
        $this->flush();
    }

    /**
     * Can be called manually to flush any cached entries.
     */
    public function flush()
    {
    }
}

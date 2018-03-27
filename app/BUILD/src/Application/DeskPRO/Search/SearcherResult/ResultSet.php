<?php

/**
 * DeskPRO.
 *
 * @category Search
 */

namespace Application\DeskPRO\Search\SearcherResult;

/**
 * Search adapter.
 */
class ResultSet implements \Countable, \IteratorAggregate
{
    /**
     * @var int
     */
    protected $total = 0;

    /**
     * Array of results.
     *
     * @var Application\DeskPRO\Search\SearcherResult\ResultInterface[]
     */
    protected $results = [];

    public function __construct($total, array $results)
    {
        $this->total   = $total;
        $this->results = $results;
    }

    /**
     * The total number of matched objects.
     *
     * @var int
     */
    public function totalCount()
    {
        return $this->total;
    }

    /**
     * How many results in this object? Note: NOT the same as total.
     *
     * @return int
     */
    public function count()
    {
        return count($this->results);
    }

    /**
     * Get a result by index, or null if the index doesnt exist.
     *
     * @return \Application\DeskPRO\Search\SearcherResult\ResultInterface
     */
    public function getResult($i)
    {
        return isset($this->results[$i]) ? $this->results[$i] : null;
    }

    /**
     * Get results.
     *
     * @var \Application\DeskPRO\Search\SearcherResult\ResultInterface[]
     */
    public function getResults()
    {
        return $this->results;
    }

    /**
     * Get iterator.
     *
     * @return \ArrayObject
     */
    public function getIterator()
    {
        return new \ArrayObject($this->results);
    }

    /**
     * If the result was cached, this is the cacheid we might use again.
     * Null means no cacheid.
     *
     * @return mixed
     */
    public function getCacheId()
    {
        return;
    }
}

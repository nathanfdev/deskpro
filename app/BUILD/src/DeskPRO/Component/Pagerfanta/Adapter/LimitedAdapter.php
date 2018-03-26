<?php

namespace DeskPRO\Component\Pagerfanta\Adapter;

use Pagerfanta\Adapter\AdapterInterface;

class LimitedAdapter implements AdapterInterface
{
    /**
     * @var int
     */
    private $limit = 0;

    /**
     * @var AdapterInterface
     */
    private $adapter;

    /**
     * LimitedAdpater constructor.
     *
     * @param AdapterInterface $adapter
     * @param int              $limit
     */
    public function __construct(AdapterInterface $adapter, $limit)
    {
        $this->adapter = $adapter;
        $this->limit   = (int) $limit;

        if ($this->limit < 0) {
            throw new \OutOfRangeException('limit must be 0 (disabled) or > 1');
        }
    }

    /**
     * @return int
     */
    public function getLimit()
    {
        return $this->limit;
    }

    /**
     * @return AdapterInterface
     */
    public function getAdapter()
    {
        return $this->adapter;
    }

    /**
     * {@inheritdoc}
     */
    public function getNbResults()
    {
        if ($this->limit) {
            return min($this->adapter->getNbResults(), $this->limit);
        }

        return $this->adapter->getNbResults();
    }

    /**
     * {@inheritdoc}
     */
    public function getSlice($offset, $length)
    {
        if (!$this->limit) {
            return $this->adapter->getSlice($offset, $length);
        }

        // Over our limit
        if ($offset > $this->limit) {
            return [];
        }

        // If the length would make us go over the limit,
        // we need to shorten the length
        $maxRec = $offset + $length;
        if ($maxRec > $this->limit) {
            $length -= $maxRec - $this->limit;
        }

        if ($length === 0) {
            return [];
        }

        return $this->adapter->getSlice($offset, $length);
    }
}

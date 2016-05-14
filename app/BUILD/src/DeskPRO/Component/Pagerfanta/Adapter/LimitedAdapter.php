<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2016, DeskPRO Ltd.
 *
 * The license agreement under which this software is released
 * can be found at https://www.deskpro.com/eula/
 *
 * By using this software, you acknowledge having read the license
 * and agree to be bound thereby.
 *
 * Please note that DeskPRO is not free software. We release the full
 * source code for our software because we trust our users to pay us for
 * the huge investment in time and energy that has gone into both creating
 * this software and supporting our customers. By providing the source code
 * we preserve our customers' ability to modify, audit and learn from our
 * work. We have been developing DeskPRO since 2001, please help us make it
 * another decade.
 *
 * Like the work you see? Think you could make it better? We are always
 * looking for great developers to join us: http://www.deskpro.com/jobs/
 *
 * ~ Thanks, Everyone at Team DeskPRO
 */

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

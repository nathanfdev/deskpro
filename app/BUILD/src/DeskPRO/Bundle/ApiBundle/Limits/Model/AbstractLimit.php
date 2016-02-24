<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2015, DeskPRO Ltd.
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

namespace DeskPRO\Bundle\ApiBundle\Limits\Model;

/**
 * Class AbstractLimit.
 */
abstract class AbstractLimit implements LimitInterface
{
    /**
     * @var int
     */
    protected $limit = 0;

    /**
     * @var int
     */
    protected $current = 0;

    /**
     * @var \DateTime
     */
    protected $start_time;

    /**
     * @var \DateInterval
     */
    protected $interval;

    /**
     * @param $limit
     *
     * @return $this
     */
    public function setLimit($limit)
    {
        $this->limit = $limit;

        return $this;
    }

    /**
     * @param \DateInterval $interval
     *
     * @return $this;
     */
    public function setInterval(\DateInterval $interval)
    {
        $this->interval = $interval;

        return $this;
    }

    /**
     *
     */
    public function replenish()
    {
        $next_replenish = clone $this->start_time;
        $next_replenish->add($this->interval);
        $current = new \DateTime();
        if ($next_replenish->getTimestamp() > $current->getTimestamp()) {
            return false;
        }
        $this->current    = $this->limit;
        $this->start_time = $current;

        return true;
    }

    /**
     *
     */
    public function reduceLimit()
    {
        --$this->current;
    }

    /**
     * @return bool
     */
    public function hasLimit()
    {
        return $this->current > 0;
    }

    public function getCurrentLimit()
    {
        return $this->current;
    }

    /**
     * @return \DateTime
     */
    public function getStartTime()
    {
        return $this->start_time;
    }

    /**
     * @param \DateTime $start_time
     *
     * @return $this
     */
    public function setStartTime(\DateTime $start_time)
    {
        $this->start_time = $start_time;

        return $this;
    }

    /**
     * @param int $current
     *
     * @return $this
     */
    public function setCurrent($current)
    {
        $this->current = $current;

        return $this;
    }
}

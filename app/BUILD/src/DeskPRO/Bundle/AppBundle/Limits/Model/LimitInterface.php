<?php

namespace DeskPRO\Bundle\AppBundle\Limits\Model;

interface LimitInterface
{
    /**
     * @param int $limit
     *
     * @return LimitInterface
     */
    public function setLimit($limit);

    /**
     * @return bool
     */
    public function hasLimit();

    /**
     * @return int
     */
    public function getLimit();

    public function reduceLimit();

    /**
     * @param int $current_limit
     *
     * @return LimitInterface
     */
    public function setCurrent($current_limit);

    /**
     * @return int
     */
    public function getCurrentLimit();

    /**
     * @param \DateTime $date
     *
     * @return mixed
     */
    public function setStartTime(\DateTime $date);

    /**
     * @return \DateTime
     */
    public function getStartTime();

    /**
     * @param \DateInterval $interval
     *
     * @return \DateInterval
     */
    public function setInterval(\DateInterval $interval);

    /**
     * @return int
     */
    public function getIntervalInSeconds();

    /**
     * @return bool
     */
    public function replenish();

    /**
     * @return string
     */
    public function getType();
}

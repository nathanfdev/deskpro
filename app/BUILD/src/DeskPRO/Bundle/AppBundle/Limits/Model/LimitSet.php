<?php

namespace DeskPRO\Bundle\AppBundle\Limits\Model;

/**
 * Class LimitSet.
 */
class LimitSet extends \SplObjectStorage
{
    /**
     * @param LimitInterface $limit
     */
    public function addLimit(LimitInterface $limit)
    {
        if ($this->offsetExists($limit)) {
            throw new \InvalidArgumentException('Limit already exists');
        }

        $this->attach($limit);
    }
}

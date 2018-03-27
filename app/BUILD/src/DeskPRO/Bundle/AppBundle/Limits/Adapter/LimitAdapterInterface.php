<?php

namespace DeskPRO\Bundle\AppBundle\Limits\Adapter;

use Application\DeskPRO\Entity\ApiKey;
use DeskPRO\Bundle\AppBundle\Limits\Model\LimitInterface;

/**
 * Interface LimitAdapterInterface.
 */
interface LimitAdapterInterface
{
    /**
     * @return \DeskPRO\Bundle\AppBundle\Limits\Model\LimitInterface[]
     */
    public function getGlobalLimits();

    /**
     * @param ApiKey $key
     *
     * @return \DeskPRO\Bundle\AppBundle\Limits\Model\LimitInterface[]
     */
    public function getKeyLimits(ApiKey $key);

    /**
     * @param \DeskPRO\Bundle\AppBundle\Limits\Model\LimitInterface $limit
     *
     * @return mixed
     */
    public function saveGlobalLimit(LimitInterface $limit);

    /**
     * @param \DeskPRO\Bundle\AppBundle\Limits\Model\LimitInterface $limit
     * @param                                                       $key
     *
     * @return mixed
     */
    public function saveKeyLimit(LimitInterface $limit, $key);
}

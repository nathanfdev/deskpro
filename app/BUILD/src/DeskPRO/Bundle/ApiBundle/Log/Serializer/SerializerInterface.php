<?php

namespace DeskPRO\Bundle\ApiBundle\Log\Serializer;

use DeskPRO\Bundle\AppBundle\Entity\ApiLog;

/**
 * Interface SerializerInterface.
 */
interface SerializerInterface
{
    /**
     * @param ApiLog $log
     *
     * @return string
     */
    public function serialize(ApiLog $log);
}

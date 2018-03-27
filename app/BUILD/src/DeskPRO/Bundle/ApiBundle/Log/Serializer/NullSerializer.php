<?php

namespace DeskPRO\Bundle\ApiBundle\Log\Serializer;

use DeskPRO\Bundle\AppBundle\Entity\ApiLog;

/**
 * Class SerializeSerializer.
 */
class NullSerializer implements SerializerInterface
{
    public function serialize(ApiLog $log)
    {
        return $log;
    }
}

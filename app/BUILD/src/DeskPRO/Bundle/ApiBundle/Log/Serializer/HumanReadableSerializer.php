<?php

namespace DeskPRO\Bundle\ApiBundle\Log\Serializer;

use DeskPRO\Bundle\AppBundle\Entity\ApiLog;

class HumanReadableSerializer implements SerializerInterface
{
    public function serialize(ApiLog $log)
    {
        return sprintf('======================= LOG ENTRY ======================
request_id: %s
uri: %s
execution time: %d
request time: %s

request data:
------------------------------------
HEADERS:
%s
----------------
POST:
%s
----------------
QUERY:
%s
------------------------------------

response data:
------------------------------------
%s
------------------------------------
response status: %d
api_key_id: %d
=======================/LOG ENTRY ======================
'.PHP_EOL,
            $log->getRequestId(),
            $log->getRequestedUri(),
            $log->getEndTime() - $log->getStartTime(),
            date('Y-m-d H:i:s', $log->getStartTime()),
            var_export($log->getRequestData()['headers'], true),
            var_export($log->getRequestData()['post'], true),
            var_export($log->getRequestData()['query'], true),
            var_export($log->getResponseData(), true),
            $log->getStatus(),
            $log->getKey() ? $log->getKey()->getId() : 'none'
        );
    }
}

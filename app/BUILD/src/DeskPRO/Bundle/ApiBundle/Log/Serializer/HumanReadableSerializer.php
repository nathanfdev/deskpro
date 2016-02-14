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

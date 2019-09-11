<?php

namespace DeskPRO\Bundle\VoiceBundle\Twilio\Rest\Proxy;

use Twilio\Rest\Api\V2010\Account\CallContext;
use Twilio\Rest\Api\V2010\Account\CallInstance;
use Twilio\Values;

/**
 * Class CallContextProxy.
 */
class CallContextProxy extends CallContext
{
    /**
     * {@inheritdoc}
     *
     * @param bool $initial
     */
    public function fetch($initial = false)
    {
        $params = Values::of([]);
        if ($initial) {
            $params['leg_idx'] = 0;
        }

        $payload = $this->version->fetch(
            'GET',
            $this->uri,
            $params
        );

        return new CallInstance(
            $this->version,
            $payload,
            $this->solution['accountSid'],
            $this->solution['sid']
        );
    }
}

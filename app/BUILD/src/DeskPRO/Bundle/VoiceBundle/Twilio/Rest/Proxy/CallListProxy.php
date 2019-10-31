<?php

namespace DeskPRO\Bundle\VoiceBundle\Twilio\Rest\Proxy;

use Twilio\Rest\Api\V2010\Account\CallList;

/**
 * Class CallListProxy.
 */
class CallListProxy extends CallList
{
    /**
     * {@inheritdoc}
     */
    public function getContext($sid)
    {
        return new CallContextProxy($this->version, $this->solution['accountSid'], $sid);
    }
}

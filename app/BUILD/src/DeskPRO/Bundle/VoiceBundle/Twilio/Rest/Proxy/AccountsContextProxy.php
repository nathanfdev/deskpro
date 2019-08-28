<?php

namespace DeskPRO\Bundle\VoiceBundle\Twilio\Rest\Proxy;

use Twilio\Rest\Api\V2010\AccountContext;

/**
 * Class AccountsContextProxy.
 */
class AccountsContextProxy extends AccountContext
{
    /**
     * {@inheritdoc}
     */
    protected function getCalls()
    {
        if (!$this->_calls) {
            $this->_calls = new CallListProxy($this->version, $this->solution['sid']);
        }

        return $this->_calls;
    }
}

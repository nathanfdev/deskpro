<?php

namespace DeskPRO\Bundle\VoiceBundle\Twilio\Rest\Proxy;

use Twilio\Rest\Api\V2010;

class V2010Proxy extends V2010
{
    /**
     * {@inheritdoc}
     */
    protected function getAccount()
    {
        if (!$this->_account) {
            $this->_account = new AccountsContextProxy(
                $this,
                $this->domain->getClient()->getAccountSid()
            );
        }

        return $this->_account;
    }
}

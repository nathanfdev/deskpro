<?php

namespace DeskPRO\Bundle\VoiceBundle\Twilio\Rest\Proxy;

use Twilio\Rest\Accounts;

/**
 * Class AccountsProxy.
 */
class AccountsProxy extends Accounts
{
    /**
     * {@inheritdoc}
     *
     * @param ClientProxy $client
     */
    public function __construct(ClientProxy $client)
    {
        parent::__construct($client);

        if ($client->getAccountsProxyUrl()) {
            $this->baseUrl = $client->getAccountsProxyUrl();
        }
    }
}

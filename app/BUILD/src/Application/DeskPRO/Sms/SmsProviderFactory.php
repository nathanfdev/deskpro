<?php

namespace Application\DeskPRO\Sms;

use Orb\Sms\Provider\ClickatellSmsProvider;
use Orb\Sms\Provider\TwilioSmsProvider;

/**
 * Class SmsProviderFactory.
 */
class SmsProviderFactory
{
    /**
     * @param string $providerName
     * @param array  $params
     *
     * @return \Orb\Sms\SmsProviderInterface
     */
    public static function create($providerName, array $params)
    {
        switch ($providerName) {
            case 'twilio':
                return new TwilioSmsProvider($params['sid'], $params['auth_token']);
            case 'clickatell':
                return new ClickatellSmsProvider($params['auth_token']);
        }

        throw new \InvalidArgumentException(
            "sms provider '$providerName' does not exist, please check logic inside of SmsProviderFactory'"
        );
    }
}
